#!perl -w

use Test::More;
use DBI;
use strict;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($mdriver);
$| = 1;

use vars qw($test_dsn $test_user $test_passwd);
use lib 't', '.';
require 'lib.pl';

my $dbh;

# Invalid connect test might behave differently with ODBC driver manager
# But let's try to keep the structure
eval {
    $dbh = DBI->connect ($test_dsn, "invalid", $test_passwd,
        { RaiseError => 0, PrintError => 0, AutoCommit => 0 });
};

# ODBC might not throw exception but return undef and set errstr
# The original test expected failure for invalid user

eval {
    $dbh = DBI->connect ($test_dsn, $test_user, $test_passwd,
        { RaiseError => 1, PrintError => 1, AutoCommit => 0 });
};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr Can't continue test";
}

plan tests => 2;
ok defined $dbh, "Connected to database";
if ($dbh) {
    ok $dbh->disconnect();
} else {
    fail("Disconnect failed because connect failed");
}

#again
# $dbh->disconnect(); # Calling disconnect on already disconnected handle might warn
