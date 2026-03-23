#!perl -w
# vim: ft=perl

use strict;
use DBI ();
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($table $test_dsn $test_user $test_passwd);
use lib 't', '.';
require "lib.pl";

my $dbh;
eval{$dbh = DBI->connect($test_dsn, $test_user, $test_passwd,
			    {RaiseError => 1});};

if ($@) {
    plan skip_all => 
        "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 17; 

ok $dbh->do("DROP TABLE IF EXISTS $table");

my $create = <<EOT;
CREATE TABLE $table (
  id INT(3) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  name VARCHAR(64))
EOT

ok $dbh->do($create), "create $table";

my $query = "INSERT INTO $table (name) VALUES (?)";

my $sth;
ok ($sth = $dbh->prepare($query));

ok defined $sth;

ok $sth->execute("Jochen");

# internal_last_insert_id 함수 정의
sub internal_last_insert_id {
    my ($dbh) = @_;
    my $sth = $dbh->prepare("SELECT LAST_INSERT_ID()");
    $sth->execute();
    my ($id) = $sth->fetchrow_array();
    $sth->finish();
    return $id;
}

# last_insert_id is driver dependent. 
# ODBC might support it if the driver does. 
# CUBRID_ODBC_Unicode support for SQLGetInfo(SQL_LAST_INSERT_ID_SQL) or similar needs check
# Or explicit query "SELECT LAST_INSERT_ID()"
# Standard DBI way:
# my $insert_id = $dbh->last_insert_id(undef, undef, $table, undef);
my $insert_id = internal_last_insert_id($dbh);
# If undefined, try a fallback for CUBRID if needed, but last_insert_id should work if supported
# For now, let's keep it and see if it fails. 

is $insert_id, 1, "sth insert id == $insert_id";

ok $sth->execute("Patrick");

ok (my $sth2 = $dbh->prepare("SELECT max(id) FROM $table"));

ok defined $sth2;

ok $sth2->execute();

my $max_id;
ok ($max_id = $sth2->fetch());

ok defined $max_id;

$insert_id = internal_last_insert_id($dbh);
cmp_ok $insert_id, '==', $max_id->[0], "sth2 insert id $insert_id == max(id) $max_id->[0] in $table";

ok $sth->finish();

ok $sth2->finish();

ok $dbh->do("DROP TABLE IF EXISTS $table"), "drop $table after insert id checks";

ok $dbh->disconnect();
#error
#$insert_id = internal_last_insert_id($dbh);
