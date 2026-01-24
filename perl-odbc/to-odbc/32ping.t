#!perl -w

use DBI;
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib 't', '.';
require 'lib.pl';

use vars qw($test_dsn $test_user $test_passwd $table);

my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 0, PrintError => 1, AutoCommit => 0 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}

# This query will likely fail as table 'code' doesn't exist, but that's probably intended
$dbh->do("SELECT * FROM code WHERE s_name = ?", undef, 'X');

plan tests => 2;

ok $dbh->ping;

$dbh->do("SELECT * FROM unknown_table");

ok $dbh->disconnect;
$dbh->ping;
