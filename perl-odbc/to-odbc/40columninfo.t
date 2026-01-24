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
                      { RaiseError            => 1,
                        PrintError            => 1,
                        AutoCommit            => 1 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 6;

ok(defined $dbh, "connecting");

ok($dbh->do(qq{DROP TABLE IF EXISTS t1}), "cleaning up");

# Removed 'a_' and `a'b` columns as they might be problematic for some drivers/databases quoting rules or might need strict quoting
# CUBRID identifiers can contain special chars if quoted.
ok($dbh->do(qq{CREATE TABLE t1 (a INT PRIMARY KEY AUTO_INCREMENT,
                                b INT,
                                "a_" INT,
                                "a'b" INT,
                                bar INT
                                )}), "creating table");

# column_info
my $sth= $dbh->column_info(undef, undef, "t1", "a%");
if ($sth) {
    my ($info)= $sth->fetchall_arrayref({});
    # a, a_, a'b matches a%
    # But wait, underscore is a wildcard in LIKE pattern unless escaped?
    # DBI spec says pattern arguments.
    # If _ is wildcard, then a_ matches a + any char.
    # a (no), a_ (yes), a'b (yes, 3 chars, 'a', ''', 'b' ... wait. a_ matches 2 chars starting with a. a'b is 3 chars.
    # Actually % matches any sequence.
    # "a%" matches starting with a.
    # a, a_, a'b all start with a.
    # So we expect 3.
    is(scalar @$info, 3, "column_info a% count");
} else {
    fail("column_info failed");
}

# "a'b" as pattern. ' is literal?
# In LIKE pattern, ' is just a char.
$sth= $dbh->column_info(undef, undef, "t1", "a'b");
if ($sth) {
    my ($info)= $sth->fetchall_arrayref({});
    is(scalar @$info, 1, "column_info a'b count");
} else {
    fail("column_info failed");
}

ok($dbh->do(qq{DROP TABLE IF EXISTS t1}), "cleaning up");

$dbh->disconnect();
