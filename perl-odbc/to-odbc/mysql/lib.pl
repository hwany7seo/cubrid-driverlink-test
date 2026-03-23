#!/usr/bin/perl
#
#   MySQL ODBC — DBI::ODBC test defaults (see odbcinst.ini driver section name).
#
use strict;
use warnings;
use utf8;
use open ':std', ':encoding(UTF-8)';

use vars qw(
  $table $table_unqualified $mysql_db
  $mdriver $dbdriver $test_dsn $test_user $test_passwd
);

$| = 1;

$mdriver  = 'ODBC';
$dbdriver = $mdriver;
$test_dsn = 'mysql80';

# odbcinst.ini: [MySQL ODBC 9.6 Unicode Driver] / [MySQL ODBC 9.6 ANSI Driver]
$ENV{MYSQL_ODBC_DRIVER} ||= 'MySQL ODBC 9.6 Unicode Driver';

$mysql_db           = $ENV{MYSQL_TEST_DATABASE} || 'testdb';
$table_unqualified  = 'test_cubrid';
$table              = "$table_unqualified";

my $server = $ENV{MYSQL_TEST_SERVER} || 'test-db-server';
my $port   = $ENV{MYSQL_TEST_PORT}   || '3306';

# Full ODBC connection string (no odbc.ini DSN entry required).
# Override entirely: export PERL_ODBC_DSN='DBI:ODBC:...'
if ($ENV{PERL_ODBC_DSN}) {
    $test_dsn = $test_dsn;
}
else {
    my $drv = $ENV{MYSQL_ODBC_DRIVER};
    $test_dsn = join '',
      'DBI:ODBC:',
      "Driver={$drv};",
      "Server=$server;",
      "Port=$port;",
      "Database=$mysql_db;",
      'Option=3;';
}

$test_user   = $ENV{MYSQL_TEST_USER}     || $ENV{DB_USER}     || 'root';
$test_passwd = $ENV{MYSQL_TEST_PASSWORD} || $ENV{DB_PASSWORD} || '';

$::COL_NULLABLE = 1;
$::COL_KEY      = 2;

sub byte_string {
    my $ret = join ('|', unpack ('C*', $_[0]));
    return $ret;
}

# Some ODBC drivers return WCHAR/fixed buffers with embedded NULs.
sub odbc_strip_nul {
    my ($s) = @_;
    return '' unless defined $s;
    $s =~ tr/\0//d;
    $s =~ s/\s+\z//;
    return $s;
}

# "owner.table" -> "table"
sub odbc_unqual_name {
    my ($s) = odbc_strip_nul($_[0]);
    $s =~ s/^[^.]+\.//;
    return $s;
}

# MySQL may return TABLE_NAME as "db.table" or only "table" for primary_key_info.
sub odbc_mysql_table_name_ok {
    my ($got) = @_;
    $got = lc odbc_strip_nul($got);
    return 0 unless length $got;
    my $full  = lc $table;
    my $short = lc $table_unqualified;
    return 1 if $got eq $full || $got eq $short;
    return 0;
}

1;
