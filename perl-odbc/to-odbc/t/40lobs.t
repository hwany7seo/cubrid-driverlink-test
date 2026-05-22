#!perl -w

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
        { RaiseError => 0, AutoCommit => 1,
          LongReadLen => 10000, LongTruncOk => 1 })};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
else {
    plan tests => 16;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    name CLOB )
EOT

ok ($dbh->do($create));

my ($sth, $query, $row);
$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));

my $bind_ok = $sth->bind_param(1, "Hello world!", DBI::SQL_CLOB);
ok ($bind_ok, "bind_param SQL_CLOB")
    or diag("bind_param SQL_CLOB: " . ($sth->errstr // $dbh->errstr // ''));

SKIP: {
    skip "SQL_CLOB bind failed (HY021 regression)", 9 unless $bind_ok;

    ok ($sth->execute, "execute after SQL_CLOB bind");
    ok ($sth->finish);

    ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 1"));
    ok ($sth->execute);
    ok ($row = $sth->fetchrow_arrayref);
    ok defined($row), "row returned defined";
    is @$row, 2, "records from $table returned 2";
    is $$row[0], 1, 'id set to 1';
    is $$row[1], "Hello world!", "CLOB content match";
    ok ($sth->finish);
}

ok $dbh->do("DROP TABLE $table"), "Drop table $table";

ok $dbh->disconnect;
