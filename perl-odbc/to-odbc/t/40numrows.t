#!perl -w

use strict;
use DBI;
use Test::More;
use Carp qw(croak);
use Data::Dumper;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($table $test_dsn $test_user $test_passwd);
use lib 't', '.';
require 'lib.pl';

my ($dbh, $sth, $aref);
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 1, PrintError => 1, AutoCommit => 0 });};
if ($@) {
    plan skip_all => 
        "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 30;

ok $dbh->do("DROP TABLE IF EXISTS $table");

my $create= <<EOT;
CREATE TABLE $table (
  id INT NOT NULL DEFAULT 0,
  name varchar(64) NOT NULL DEFAULT ''
) 
EOT

ok $dbh->do($create), "CREATE TABLE $table";

ok $dbh->do("INSERT INTO $table VALUES( 1, 'Alligator Descartes' )"), 'inserting first row';

ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 1"));

ok $sth->execute;

# $sth->rows is not reliable for SELECT in some drivers/DBs (incl ODBC sometimes)
# But let's check it. CUBRID ODBC driver might return correct count if cursor type allows or after fetch.
# DBI spec says rows() for SELECT is unknown (-1) until fetch, or driver dependent.
# If it fails, we might need to change test or note it.
# is $sth->rows, 1, '$sth->rows should be 1';
# Just check defined for safety or skip strict check if ODBC returns -1
my $rows = $sth->rows;
if ($rows == -1) {
    pass('$sth->rows is -1 (unknown) which is allowed for SELECT');
} else {
    is $rows, 1, '$sth->rows should be 1';
}

ok ($aref= $sth->fetchall_arrayref);

is scalar @$aref, 1, 'Verified rows should be 1';

ok $sth->finish;

ok $dbh->do("INSERT INTO $table VALUES( 2, 'Jochen Wiedmann' )"), 'inserting second row';

ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id >= 1"));

ok $sth->execute;

$rows = $sth->rows;
if ($rows == -1) {
    pass('$sth->rows is -1');
} else {
    is $rows, 2, '$sth->rows should be 2';
}

ok ($aref= $sth->fetchall_arrayref);

is scalar @$aref, 2, 'Verified rows should be 2';

ok $sth->finish;

ok $dbh->do("INSERT INTO $table VALUES(3, 'Tim Bunce')"), "inserting third row";

ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id >= 2"));

ok $sth->execute;

$rows = $sth->rows;
if ($rows == -1) {
    pass('$sth->rows is -1');
} else {
    is $rows, 2, 'rows should be 2';
}

ok ($aref= $sth->fetchall_arrayref);

is scalar @$aref, 2, 'Verified rows should be 2';

ok $sth->finish;

ok ($sth = $dbh->prepare("SELECT * FROM $table"));

ok $sth->execute;

$rows = $sth->rows;
if ($rows == -1) {
    pass('$sth->rows is -1');
} else {
    is $rows, 3, 'rows should be 3';
}

ok ($aref= $sth->fetchall_arrayref);

is scalar @$aref, 3, 'Verified rows should be 3';

ok $dbh->do("DROP TABLE $table"), "drop table $table";

ok $dbh->disconnect;
