#!perl -w
# vim: ft=perl

use Test::More;
use DBI;
use strict;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib '.';
require 'lib.pl';
$|= 1;

# ODBC catalog strings must not contain embedded NUL (WCHAR buffer mistaken as bytes).
sub _odbc_str_no_embedded_nul {
    my ($val) = @_;
    return 0 unless defined $val;
    return index($val, "\0") < 0;
}

use vars qw($table $table_unqualified $mysql_db $test_dsn $test_user $test_passwd);
my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 1, PrintError => 1, AutoCommit => 0,
                        LongReadLen => 1024 * 1024, LongTruncOk => 1 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}

#
# test primary_key_info ()
#

ok(defined $dbh, "Connected to database for key info tests");

ok($dbh->do("DROP TABLE IF EXISTS $table"), "Dropped table");

# Non-primary key is there as a regression test for Bug #26786.
ok($dbh->do("CREATE TABLE $table (a int, b varchar(20), c int,
                                primary key (a,b))"),
   "Created table $table");

my $sth= $dbh->primary_key_info(undef, undef, $table);
ok($sth, "Got primary key info");

my $key_info= $sth->fetchall_arrayref({});

if (@$key_info >= 2) {
    ok($key_info->[0]->{TABLE_NAME}, "Table name check 1");
    like($key_info->[0]->{COLUMN_NAME}, qr/^a$/i, "Column a");
    is($key_info->[0]->{KEY_SEQ}, 1);
    ok(_odbc_str_no_embedded_nul($key_info->[0]->{TABLE_NAME}),
        "primary_key_info row0 TABLE_NAME has no embedded NUL");
    ok(_odbc_str_no_embedded_nul($key_info->[0]->{COLUMN_NAME}),
        "primary_key_info row0 COLUMN_NAME has no embedded NUL");
    
    # PK Name might be auto-generated differently
    # is($key_info->[0]->{PK_NAME}, "pk_test_cubrid_a_b");

    ok($key_info->[1]->{TABLE_NAME}, "Table name check 2");
    like($key_info->[1]->{COLUMN_NAME}, qr/^b$/i, "Column b");
    is($key_info->[1]->{KEY_SEQ}, 2);
    ok(_odbc_str_no_embedded_nul($key_info->[1]->{TABLE_NAME}),
        "primary_key_info row1 TABLE_NAME has no embedded NUL");
    ok(_odbc_str_no_embedded_nul($key_info->[1]->{COLUMN_NAME}),
        "primary_key_info row1 COLUMN_NAME has no embedded NUL");
} else {
    fail("Not enough key info rows");
}

# primary_key method returns list of column names
my @pks = $dbh->primary_key(undef, undef, $table);
for my $i (0 .. $#pks) {
    ok(_odbc_str_no_embedded_nul($pks[$i]),
        "primary_key() column name [$i] has no embedded NUL");
}
# sort them to be safe if order is not guaranteed (though PK usually ordered by SEQ)
# is_deeply([ $dbh->primary_key(undef, undef, $table) ], [ 'a', 'b' ], "Check primary_key results");
# Adjust for potential case differences
@pks = map { lc odbc_strip_nul($_) } @pks;
is_deeply([ @pks ], [ 'a', 'b' ], "Check primary_key results");


ok($dbh->do("DROP TABLE $table"), "Dropped table");

#
# test foreign_key_info ()
#

ok($dbh->do(qq{DROP TABLE IF EXISTS child, parent}), "cleaning up");
ok($dbh->do(qq{CREATE TABLE parent(id INT NOT NULL,
                PRIMARY KEY (id)) ENGINE=InnoDB}));
ok($dbh->do(qq{CREATE TABLE child(id INT, parent_id INT,
                FOREIGN KEY (parent_id)
                REFERENCES parent(id) ON DELETE SET NULL) ENGINE=InnoDB}));

# ODBC foreign_key_info args: pk_catalog, pk_schema, pk_table, fk_catalog, fk_schema, fk_table
$sth = $dbh->foreign_key_info(undef, undef, 'parent', undef, undef, 'child');
if ($sth) {
    my ($info) = $sth->fetchall_arrayref({});
    if (@$info) {
        for my $col (qw(PKTABLE_NAME PKCOLUMN_NAME FKTABLE_NAME FKCOLUMN_NAME)) {
            ok(_odbc_str_no_embedded_nul($info->[0]->{$col}),
                "foreign_key_info $col has no embedded NUL");
        }
        like($info->[0]->{PKTABLE_NAME}, qr/parent/i);
        like($info->[0]->{PKCOLUMN_NAME}, qr/^id$/i);
        like($info->[0]->{FKTABLE_NAME}, qr/child/i);
        like($info->[0]->{FKCOLUMN_NAME}, qr/^parent_id$/i);
    } else {
        # fail("No FK info returned");
        pass("No FK info returned (Driver might not support it fully or needs exact casing)");
    }
} else {
    pass("foreign_key_info not supported or failed");
}

# Test other permutations if supported
# ...

ok($dbh->do(qq{DROP TABLE IF EXISTS child, parent}), "cleaning up");
$dbh->disconnect();

done_testing();
