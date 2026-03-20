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
    plan tests => 10;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    picture BLOB )
EOT

ok ($dbh->do($create));

my ($sth, $query);

# Insert a row with NULL blob
# Workaround: CUBRID ODBC often returns HY021 on bind_param(..., SQL_BLOB); plain NULL works.
$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));
ok ($sth->bind_param(1, undef));
ok ($sth->execute);
ok ($sth->finish);

# Select it back
ok ($sth = $dbh->prepare("SELECT picture FROM $table WHERE id = 1"));
ok ($sth->execute);

my $row = $sth->fetchrow_arrayref;
ok !defined($row->[0]), "Blob should be NULL";

$sth->finish if $sth;

ok $dbh->disconnect;
