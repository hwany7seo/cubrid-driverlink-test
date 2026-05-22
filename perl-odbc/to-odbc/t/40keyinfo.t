#!perl -w
# vim: ft=perl

use Test::More;
use DBI;
use strict;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib 't', '.';
require 'lib.pl';
$|= 1;

use vars qw($table $test_dsn $test_user $test_passwd);
my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 1, PrintError => 1, AutoCommit => 0,
                        LongReadLen => 1024 * 1024, LongTruncOk => 1 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}

plan tests => 22;

#
# test primary_key_info ()
#

ok(defined $dbh, "Connected to database for key info tests");

ok($dbh->do("DROP TABLE IF EXISTS $table"), "Dropped table");

# Non-primary key is there as a regression test for Bug #26786.
ok($dbh->do("CREATE TABLE $table (a int, b varchar(20), c int,
                                primary key (a,b))"),
   "Created table $table");

my $sth= $dbh->primary_key_info(undef, undef, $table);
ok($sth, "Got primary key info");

my $key_info= $sth->fetchall_arrayref({});

if (@$key_info >= 2) {
    is($key_info->[0]->{TABLE_NAME}, $table, "Table name check 1");
    is($key_info->[0]->{COLUMN_NAME}, 'a', "Column a");
    is($key_info->[0]->{KEY_SEQ}, 1);

    is($key_info->[1]->{TABLE_NAME}, $table, "Table name check 2");
    is($key_info->[1]->{COLUMN_NAME}, 'b', "Column b");
    is($key_info->[1]->{KEY_SEQ}, 2);
} else {
    fail("Not enough key info rows");
}

# primary_key method returns list of column names
is_deeply([ $dbh->primary_key(undef, undef, $table) ], [ 'a', 'b' ],
    "Check primary_key results");


ok($dbh->do("DROP TABLE $table"), "Dropped table");

#
# test foreign_key_info ()
#

ok($dbh->do(qq{DROP TABLE IF EXISTS child, parent}), "cleaning up");
ok($dbh->do(qq{CREATE TABLE parent(id INT NOT NULL,
                PRIMARY KEY (id))}));
ok($dbh->do(qq{CREATE TABLE child(id INT, parent_id INT,
                FOREIGN KEY (parent_id)
                REFERENCES parent(id) ON DELETE SET NULL)}));

# ODBC foreign_key_info args: pk_catalog, pk_schema, pk_table, fk_catalog, fk_schema, fk_table
$sth = $dbh->foreign_key_info(undef, undef, 'parent', undef, undef, 'child');
ok($sth, "Got foreign key info");
my ($info) = $sth->fetchall_arrayref({});
ok(@$info, "Foreign key info returned");
is($info->[0]->{PKTABLE_NAME}, 'parent');
is($info->[0]->{PKCOLUMN_NAME}, 'id');
is($info->[0]->{FKTABLE_NAME}, 'child');
is($info->[0]->{FKCOLUMN_NAME}, 'parent_id');

# Test other permutations if supported
# ...

ok($dbh->do(qq{DROP TABLE IF EXISTS child, parent}), "cleaning up");
$dbh->disconnect();
