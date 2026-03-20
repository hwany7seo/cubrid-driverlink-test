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
                      { RaiseError => 1, PrintError => 1, AutoCommit => 1 });};
if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}

plan tests => 9;

ok ($dbh->do("DROP TABLE IF EXISTS $table"));

my $create = <<EOT;
CREATE TABLE $table (
        id int NOT NULL default 0,
        name varchar(64) default ''
        )
EOT

ok ($dbh->do($create));

ok ($sth = $dbh->prepare("INSERT INTO $table VALUES (?, ?)"));

ok ($sth->bind_param(1, 1));
# Binding null.
# undef in bind_param treats as NULL.
ok ($sth->bind_param(2, undef));
ok ($sth->execute);

# Verify
my $row = $dbh->selectrow_arrayref("SELECT name FROM $table WHERE id = 1");
ok !defined($row->[0]), "Name should be NULL";

ok $sth->finish;
ok $dbh->disconnect;
