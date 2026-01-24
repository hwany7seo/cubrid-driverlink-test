#!/usr/bin/perl
#
#   lib.pl is the file where database specific things should live,
#   whereever possible. For example, you define certain constants
#   here and the like.
#
# All this code is subject to being GUTTED soon
#
use strict;
use warnings;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($table $mdriver $dbdriver $test_dsn $test_user $test_passwd);
$table= 'test_cubrid';

$| = 1; # flush stdout asap to keep in sync with stderr

#
#   Driver names; EDIT THIS!
#

$mdriver = 'ODBC'; # Changed to ODBC
$dbdriver = $mdriver; 


#
#   DSN being used; do not edit this, edit "$dbdriver.dbtest" instead
#

$test_dsn = 'DBI:ODBC:CUBRID Driver';
$test_user = $ENV{DB_USER} || "dba";
$test_passwd = $ENV{DB_PASSWORD} || "";

$::COL_NULLABLE = 1;
$::COL_KEY = 2;

sub byte_string {
    my $ret = join ("|", unpack ("C*", $_[0]));
    return $ret;
}

1;
