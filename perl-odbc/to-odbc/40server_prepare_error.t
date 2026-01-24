#!perl -w
# vim: ft=perl
# Test problem in 3.0002_4 and 3.0005 where if a statement is prepared
# and multiple executes are performed, if any execute fails all subsequent
# executes report an error but may have worked.

use strict;
use DBI;
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib '.', 't';
require 'lib.pl';

use vars qw($test_dsn $test_user $test_passwd);

# ODBC might not support mysql_server_prepare param. 
# This test seems MySQL specific (mysql_server_prepare).
# CUBRID ODBC driver handles preparation.
# $test_dsn .= ";mysql_server_prepare=1";

my $dbh;
eval {$dbh = DBI->connect($test_dsn, $test_user, $test_passwd,
  { RaiseError => 1, AutoCommit => 1})};

if ($@) {
    plan skip_all => "ERROR: $@. Can't continue test";
}

plan tests => 3;

# execute invalid SQL to make sure we get an error
my $q = "select select select";	# invalid SQL
$dbh->{PrintError} = 0;
# PrintWarn is not standard DBI attribute, but might be supported by DBD::mysql/cubrid
# DBD::ODBC might ignore it or warn
# $dbh->{PrintWarn} = 0;

my $sth;
eval {$sth = $dbh->prepare($q);};
$dbh->{PrintError} = 1;
# $dbh->{PrintWarn} = 1;

ok defined($DBI::errstr), "DBI::errstr defined";
cmp_ok $DBI::errstr, 'ne', '', "DBI::errstr not empty";

if ($DBI::errstr) {
    my $errstr = $DBI::errstr;
    $errstr =~ s/\n/  /g;
    print "Excepted msg -- [$errstr]\n";
}
ok $dbh->disconnect();
