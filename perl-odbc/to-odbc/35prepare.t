#!perl -w
# vim: ft=perl

use strict;
use Test::More;
use DBI;
use Carp qw(croak);
use Data::Dumper;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib 't', '.';
require 'lib.pl';

my ($row, $sth, $dbh);
my ($table, $def, $rows, $errstr, $ret_ref);
use vars qw($table $test_dsn $test_user $test_passwd);

eval {$dbh = DBI->connect($test_dsn, $test_user, $test_passwd,
    { RaiseError => 1, AutoCommit => 1});};

if ($@) {
    plan skip_all => 
        "Can't connect to database ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 50; 

ok(defined $dbh, "Connected to database");

ok($dbh->do("DROP TABLE IF EXISTS t1"), "Making slate clean");

ok($dbh->do("CREATE TABLE t1 (id INT, name VARCHAR(64))"),
  "Creating table"); # Changed INT(4) to INT

# ODBC way to check tables might differ, but try SHOW TABLES or standard table_info
# CUBRID supports SHOW TABLES
ok($sth = $dbh->prepare("SHOW TABLES LIKE 't1'"),
  "Testing prepare show tables");

ok($sth->execute(), "Executing 'show tables'");

ok((defined($row= $sth->fetchrow_arrayref) &&
  (!defined($errstr = $sth->errstr) || $sth->errstr eq '')),
  "Testing if result set and no errors");

# CUBRID SHOW TABLES returns table name in the first column
# Depending on ODBC driver/config, it might return differently formatted name (e.g. pattern match)
# But standard CUBRID SQL 'SHOW TABLES' usually returns 'name' column.
# Let's check if we got 't1' or similar
if ($row) {
    ok($row->[0] eq 't1' || $row->[0] eq 'public.t1', "Checking if results equal to 't1' or 'public.t1' (Got: $row->[0]) \n");
} else {
    fail("No row returned for SHOW TABLES");
}

ok($sth->finish, "Finishing up with statement handle");

ok($dbh->do("INSERT INTO t1 VALUES (1,'1st first value')"),
  "Inserting first row");

ok($sth= $dbh->prepare("INSERT INTO t1 VALUES (2,'2nd second value')"),
  "Preparing insert of second row");

ok(($rows = $sth->execute()), "Inserting second row");

ok($rows == 1, "One row should have been inserted");

ok($sth->finish, "Finishing up with statement handle");

ok($sth= $dbh->prepare("SELECT id, name FROM t1 WHERE id = 1"), 
  "Testing prepare of query");

ok($sth->execute(), "Testing execute of query");

ok($ret_ref = $sth->fetchall_arrayref(),
  "Testing fetchall_arrayref of executed query");

ok($sth= $dbh->prepare("INSERT INTO t1 values (?, ?)"),
  "Preparing insert, this time using placeholders");
	
my $testInsertVals = {};
for (my $i = 0 ; $i < 10; $i++)
{ 
  my @chars = grep !/[0O1Iil]/, 0..9, 'A'..'Z', 'a'..'z';
  my $random_chars= join '', map { $chars[rand @chars] } 0 .. 16;
   # save these values for later testing
  $testInsertVals->{$i}= $random_chars;
  ok($rows= $sth->execute($i, $random_chars), "Testing insert row");
  ok($rows= 1, "Should have inserted one row");
}

ok($sth->finish, "Testing closing of statement handle");

ok($sth= $dbh->prepare("SELECT * FROM t1 WHERE id = ? OR id = ?"),
  "Testing prepare of query with placeholders");

ok($rows = $sth->execute(1,2),
  "Testing execution with values id = 1 or id = 2");

ok($ret_ref = $sth->fetchall_arrayref(),
  "Testing fetchall_arrayref (should be four rows)");

if ($ret_ref) {
    print "RETREF " . scalar @$ret_ref . "\n";
    ok(@{$ret_ref} == 4 , "\$ret_ref should contain four rows in result set");
} else {
    fail("fetchall_arrayref failed");
}

ok $dbh->selectrow_array("SELECT * FROM t1 WHERE id =1");

ok($sth= $dbh->prepare("DROP TABLE IF EXISTS t1"),
  "Testing prepare of dropping table");

ok($sth->execute(), "Executing drop table");

# Bug #20153: Fetching all data from a statement handle does not mark it 
# as finished
ok($sth= $dbh->prepare("SELECT 1"), "Prepare - Testing bug #20153");
ok($sth->execute(), "Execute - Testing bug #20153");
ok($sth->fetchrow_arrayref(), "Fetch - Testing bug #20153");
ok(!($sth->fetchrow_arrayref()),"Not Fetch - Testing bug #20153");

# Install a handler so that a warning about unfreed resources gets caught
$SIG{__WARN__} = sub { die @_ };

ok($dbh->disconnect(), "Testing disconnect");
