#!perl -w

use File::Spec;

use DBI ();
use DBI::Const::GetInfoType;
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($table $test_dsn $test_user $test_passwd);
use lib '.', 't';
require 'lib.pl';

my $dbh;

eval {$dbh = DBI->connect($test_dsn, $test_user, $test_passwd,
        { RaiseError => 0, AutoCommit => 1})};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
else {
    plan tests => 11;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    picture BLOB )
EOT

ok ($dbh->do($create));

my ($sth, $query, $row, $bind_ok);

$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));
$bind_ok = $sth->bind_param(1, undef, DBI::SQL_BLOB);
ok ($bind_ok, "bind_param NULL SQL_BLOB")
    or diag("bind_param NULL SQL_BLOB: " . ($sth->errstr // $dbh->errstr // ''));

SKIP: {
    skip "NULL SQL_BLOB bind failed (HY021 regression)", 2 unless $bind_ok;
    ok ($sth->execute, "execute NULL SQL_BLOB bind");
    ok ($sth->finish);
}

SKIP: {
    skip "NULL SQL_BLOB bind failed (HY021 regression)", 3 unless $bind_ok;

    ok ($sth = $dbh->prepare("SELECT picture FROM $table WHERE id = 1"));
    ok ($sth->execute);
    $row = $sth->fetchrow_arrayref;
    ok ($row && !defined($row->[0]), "Blob should be NULL");
    $sth->finish if $sth;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table $table";

ok $dbh->disconnect;
