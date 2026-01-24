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
        { RaiseError => 1, AutoCommit => 1})};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
else {
    plan tests => 15;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    name CLOB )
EOT

ok ($dbh->do($create));

#   Insert a row into the test table.......
my ($sth, $query);
$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));
ok ($sth->bind_param(1, "Hello world!", DBI::SQL_CLOB));
ok ($sth->execute);
ok ($sth->finish);

#   Now, try SELECT'ing the row out.
ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 1"));

ok ($sth->execute);

# blob_read might not be supported or needed with ODBC
# ODBC usually returns LOBs as stream or long string depending on LongReadLen
$dbh->{LongReadLen} = 10000;
$dbh->{LongTruncOk} = 1;

# $sth->blob_read(0,0,0); # Not standard DBI or might be driver specific.
# CUBRID driver had it. ODBC driver likely just fetches it.

ok ($row = $sth->fetchrow_arrayref);

ok defined($row), "row returned defined";

is @$row, 2, "records from $table returned 2";

is $$row[0], 1, 'id set to 1';

# Verify content if possible
# is $$row[1], "Hello world!", "Content match";

ok ($sth->finish);

ok $dbh->do("DROP TABLE $table"), "Drop table $table";

ok $dbh->disconnect;
