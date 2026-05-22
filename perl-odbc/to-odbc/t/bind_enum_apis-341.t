#!perl -w

use DBI ();
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib 't', '.';
require 'lib.pl';
use vars qw($table $test_dsn $test_user $test_passwd);

my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 0, PrintError => 0, AutoCommit => 1 });};
if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}

plan tests => 18;

ok ($dbh->do("DROP TABLE IF EXISTS $table"));

my $create = <<EOT;
CREATE TABLE $table (
        id int(4) NOT NULL default 0,
        name varchar(64) default '',
        answers enum('yes', 'no', 'cancel')
        )
EOT

ok ($dbh->do($create));

ok ($sth = $dbh->prepare("INSERT INTO $table VALUES (?, ?, ?)"));

ok ($sth->bind_param(1, "1", DBI::SQL_INTEGER));
ok ($sth->bind_param(2, 'Andreas Koenig', {TYPE => DBI::SQL_VARCHAR}));
ok ($sth->bind_param(3, 'no', DBI::SQL_VARCHAR));
ok ($sth->execute);

ok ($sth->bind_param(1, 2, DBI::SQL_INTEGER));
ok ($sth->bind_param(2, 'Jack'));
# Regression: ENUM index bind via SQL_INTEGER (index 1 == 'yes', native DBD::cubrid).
ok ($sth->bind_param(3, 1, DBI::SQL_INTEGER), "bind ENUM by SQL_INTEGER index");
my $exec_ok = $sth->execute;
ok ($exec_ok, "execute after ENUM SQL_INTEGER bind")
    or diag("execute after ENUM SQL_INTEGER bind: " . ($sth->errstr // $dbh->errstr // ''));
if ($exec_ok) {
    is ($dbh->selectrow_array("SELECT answers FROM $table WHERE id=2"), 'yes',
        "ENUM SQL_INTEGER index 1 stored as yes");
} else {
    fail("ENUM SQL_INTEGER index 1 stored as yes");
}

ok ($sth->bind_param(1, 3));
ok ($sth->bind_param(2, 'Jerry'));
# Native DBD::cubrid: no TYPE on string ENUM after INTEGER bind on same slot.
ok ($sth->bind_param(3, 'cancel'));
my $exec3_ok = $sth->execute;
ok ($exec3_ok, "execute after untyped ENUM string bind")
    or diag("execute after untyped ENUM string bind: " . ($sth->errstr // $dbh->errstr // ''));

ok $sth->finish;
ok $dbh->disconnect;
