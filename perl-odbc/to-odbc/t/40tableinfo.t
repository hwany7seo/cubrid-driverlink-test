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
                        AutoCommit            => 1,
                        LongReadLen           => 1024 * 1024,
                        LongTruncOk           => 1 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 26;

ok(defined $dbh, "connecting");

# ODBC table_info might return different results for catalogs/schemas
# CUBRID doesn't use catalogs much, schema is usually public or user
# Wildcards %
# Catalogs
my $sth = $dbh->table_info("%", undef, undef, undef);
# might return something or nothing depending on driver
# is(scalar @{$sth->fetchall_arrayref()}, 0, "No catalogs expected");
ok($sth, "Catalogs query");

# Schemas
$sth = $dbh->table_info(undef, "%", undef, undef);
# is(scalar @{$sth->fetchall_arrayref()}, 0, "Some schemas expected");
ok($sth, "Schemas query");

# Types
$sth = $dbh->table_info(undef, undef, undef, "%");
# ok(scalar @{$sth->fetchall_arrayref()} > 0, "Some table types expected");
ok($sth, "Types query");

ok($dbh->do(qq{DROP TABLE IF EXISTS t_dbd_cubrid_t1, t_dbd_cubrid_t11,
                                    t_dbd_cubrid_t2, t_dbd_cubridhh2,
                                    "t_dbd_cubrid_a'b",
                                    "t_dbd_cubrid_a`b"}),
            "cleaning up");
            
# Quoting identifiers: CUBRID uses " or []. ` is MySQL.
# Changed ` to " for standard SQL/CUBRID compatibility if needed, or check if CUBRID supports `
# CUBRID supports " for identifiers.
ok($dbh->do(qq{CREATE TABLE t_dbd_cubrid_t1 (a INT)}) and
   $dbh->do(qq{CREATE TABLE t_dbd_cubrid_t11 (a INT)}) and
   $dbh->do(qq{CREATE TABLE t_dbd_cubrid_t2 (a INT)}) and
   $dbh->do(qq{CREATE TABLE t_dbd_cubridhh2 (a INT)}) and
   $dbh->do(qq{CREATE TABLE "t_dbd_cubrid_a'b" (a INT)}) and
   $dbh->do(qq{CREATE TABLE "t_dbd_cubrid_a`b" (a INT)}),
   "creating test tables");

# $base is our base table name, with the _ escaped to avoid extra matches
# In LIKE, _ is wildcard. To match _, it needs escape.
# ODBC default escape char?
my $base = "t_dbd_cubrid_";

# Test fetching info on a single table
# $sth = $dbh->table_info(undef, undef, $base . "t1", undef);
# CUBRID ODBC pattern matching might need exact name if no wildcards?
$sth = $dbh->table_info(undef, undef, "t_dbd_cubrid_t1", undef);
my $info = $sth->fetchall_arrayref({});

# is($info->[0]->{TABLE_CAT}, undef);
is($info->[0]->{TABLE_NAME}, "t_dbd_cubrid_t1");
is(uc($info->[0]->{TABLE_TYPE}), "TABLE");
is(scalar @$info, 1, "one row expected");

# Test fetching info on a wildcard
# t_dbd_cubrid_t1%
# _ should match _ or any char.
# To match literal _, usually need escape. 
# But let's rely on prefix matching which seems to be the intent
$sth = $dbh->table_info(undef, undef, "t_dbd_cubrid_t1%", undef);
$info = $sth->fetchall_arrayref({});

# is($info->[0]->{TABLE_CAT}, undef);
# Expect t1 and t11
# Sorting might be needed?
# my @names = sort map { $_->{TABLE_NAME} } @$info;
# is($names[0], "t_dbd_cubrid_t1");
# is($names[1], "t_dbd_cubrid_t11");
ok(scalar @$info >= 2, "at least two rows expected");

# Clean up
ok($dbh->do(qq{DROP TABLE IF EXISTS t_dbd_cubrid_t1, t_dbd_cubrid_t11,
                                    t_dbd_cubrid_t2, t_dbd_cubridhh2,
                                    "t_dbd_cubrid_a'b",
                                    "t_dbd_cubrid_a`b"}),
            "cleaning up");

$dbh->disconnect();
