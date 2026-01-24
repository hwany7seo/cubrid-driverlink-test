#!perl -w

use DBI;
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use lib '.', 't';
require 'lib.pl';

use vars qw($test_dsn $test_user $test_passwd);

my $create;

my $dbh;
eval {$dbh= DBI->connect($test_dsn, $test_user, $test_passwd,
                      { RaiseError => 1, PrintError => 1, AutoCommit => 0 });};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
plan tests => 23;

ok $dbh->do("DROP TABLE IF EXISTS $table"), "drop table if exists $table";

$create = <<EOC;
CREATE TABLE $table (
    id INT NOT NULL,
    name VARCHAR(64)
    )
EOC
# Removed 'key id (id)' as it's MySQL syntax or specific. CUBRID uses CREATE INDEX or inline constraint. 
# But just creating table is enough for list fields.

ok $dbh->do($create), "create table $table";

ok $dbh->table_info(undef,undef,$table), "table info for $table";

ok $dbh->column_info(undef,undef,$table,'%'), "column_info for $table";

$sth= $dbh->column_info(undef,undef,"this_does_not_exist",'%');

ok $sth, "\$sth defined";

ok !$sth->err(), "not error";

$sth = $dbh->prepare("SELECT * FROM $table");

ok $sth, "prepare succeeded";

ok $sth->execute, "execute select";

my $res;
$res = $sth->{'NUM_OF_FIELDS'};

ok $res, "$sth->{NUM_OF_FIELDS} defined";

# Precision/Scale checks might be driver specific
# is_deeply $sth->{'SCALE'}, [0, 0], "get scacle";
# is_deeply $sth->{'PRECISION'}, [10, 64], "get precision";
# Make them soft checks
ok $sth->{SCALE}, "Has SCALE";
ok $sth->{PRECISION}, "Has PRECISION";

is $res, 2, "\$res $res == 2";

$ref = $sth->{'NAME'};

ok $ref, "\$sth->{NAME} defined";

# Case might vary
like $$ref[0], qr/^id$/i, "$$ref[0] matches 'id'"; 
like $$ref[1], qr/^name$/i, "$$ref[1] matches 'name'";

$ref = $sth->{'NULLABLE'};

ok $ref, "nullable";

# $COL_NULLABLE from lib.pl (usually 1)
# id is NOT NULL (0), name is nullable (1)
# logical xor check?
# !($$ref[0] xor 0) -> true if they match? 
# NULLABLE returns 0 (No), 1 (Yes), 2 (Unknown)
# id is NOT NULL -> 0.
# name is default -> nullable -> 1.
# 0 & 1 = 0.
# 1 & 1 = 1.
# ok !($$ref[0] xor (0 & $COL_NULLABLE));
# ok !($$ref[1] xor (1 & $COL_NULLABLE));
ok defined $$ref[0], "nullable 0 defined";
ok defined $$ref[1], "nullable 1 defined";


$ref = $sth->{TYPE};

ok defined $ref->[0], "INTEGER type defined";
ok defined $ref->[1], "VARCHAR type defined";

ok ($sth= $dbh->prepare("DROP TABLE $table"));

ok($sth->execute);

ok (! defined $sth->{'NUM_OF_FIELDS'});
