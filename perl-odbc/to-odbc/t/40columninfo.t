#!perl -w
# vim: ft=perl

use Data::Dumper;
use Test::More;
use DBI;
use DBI::Const::GetInfoType;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib '.', 't';
require 'lib.pl';
use strict;
$|= 1;

use vars qw($table $test_dsn $test_user $test_passwd);

my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError            => 0,
                        PrintError            => 0,
                        AutoCommit            => 1,
                        LongReadLen           => 1024 * 1024,
                        LongTruncOk           => 1 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 6;

ok(defined $dbh, "connecting");

ok($dbh->do(qq{DROP TABLE IF EXISTS t1}), "cleaning up");

ok($dbh->do(qq{CREATE TABLE t1 (a INT PRIMARY KEY AUTO_INCREMENT,
                                b INT,
                                "a_" INT,
                                "a'b" INT,
                                bar INT
                                )}), "creating table");

my $sth = $dbh->column_info(undef, undef, "t1", "a%");
if ($sth) {
    my ($info) = $sth->fetchall_arrayref({});
    is(scalar @$info, 3, "column_info a% count");
} else {
    fail("column_info a% failed");
}

# Regression: column_info pattern with quote in name (native DBD::cubrid uses "[a'b]").
$sth = $dbh->column_info(undef, undef, "t1", "[a'b]");
if ($sth) {
    my ($info) = $sth->fetchall_arrayref({});
    is(scalar @$info, 1, "column_info [a'b] count");
} else {
    ok(0, "column_info [a'b] count");
    diag("column_info [a'b]: " . ($dbh->errstr // ''));
}

ok($dbh->do(qq{DROP TABLE IF EXISTS t1}), "cleaning up");

$dbh->disconnect();
