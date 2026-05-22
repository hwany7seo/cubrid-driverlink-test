#!perl -w

use File::Spec;

use DBI ();
use DBI::Const::GetInfoType;
use Test::More;
use utf8;
use open ':std', ':encoding(UTF-8)';
use vars qw($table $test_dsn $test_user $test_passwd);
use lib '.', 't';
require 'lib.pl';

my $volume;
my $script_directory;
my $abs_path = File::Spec->rel2abs($0);
($volume, $script_directory, undef) = File::Spec->splitpath($abs_path);

my $dbh;

eval {$dbh = DBI->connect($test_dsn, $test_user, $test_passwd,
        { RaiseError => 0, AutoCommit => 1,
          LongReadLen => 2_000_000, LongTruncOk => 1 })};

if ($@) {
    plan skip_all => "ERROR: $DBI::errstr. Can't continue test";
}
else {
    plan tests => 19;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    picture BLOB )
EOT

ok ($dbh->do($create));

my ($sth, $query, $row, $null_row, $bind_ok, $null_bind_ok);

my $test_png_file = File::Spec->catfile($volume, $script_directory, "cubrid_logo.png");
if (! -e $test_png_file) {
    $test_png_file = File::Spec->catfile($volume, $script_directory, "../cubrid-perl/t/cubrid_logo.png");
}

my $blob_data = "DUMMY_BLOB_DATA";
if (-e $test_png_file) {
    open my $fh, '<', $test_png_file or die "Can't open $test_png_file: $!";
    binmode $fh;
    local $/;
    $blob_data = <$fh>;
    close $fh;
}

$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));
$bind_ok = $sth->bind_param(1, $blob_data, DBI::SQL_BLOB);
ok ($bind_ok, "bind_param SQL_BLOB")
    or diag("bind_param SQL_BLOB: " . ($sth->errstr // $dbh->errstr // ''));

SKIP: {
    skip "SQL_BLOB bind failed (HY021 regression)", 1 unless $bind_ok;
    ok ($sth->execute, "execute after SQL_BLOB bind");
}

$query = "INSERT INTO $table VALUES(2, ?)";
ok ($sth = $dbh->prepare($query));
$null_bind_ok = $sth->bind_param(1, undef, DBI::SQL_BLOB);
ok ($null_bind_ok, "bind_param NULL SQL_BLOB")
    or diag("bind_param NULL SQL_BLOB: " . ($sth->errstr // $dbh->errstr // ''));

SKIP: {
    skip "NULL SQL_BLOB bind failed (HY021 regression)", 1 unless $null_bind_ok;
    ok ($sth->execute, "execute NULL SQL_BLOB bind");
}
ok ($sth->finish);

SKIP: {
    skip "BLOB insert failed (HY021 regression)", 4 unless $bind_ok;

    ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 1"), "prepare to select picture");
    ok ($sth->execute, "executing...");
    $row = $sth->fetchrow_arrayref;
    ok ($row && defined($row->[1]), "Got blob data");
    is ($row->[1], $blob_data, "BLOB content match");
}

SKIP: {
    skip "NULL BLOB insert failed (HY021 regression)", 3 unless $null_bind_ok;

    ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 2"), "prepare to select NULL blob");
    ok ($sth->execute, "executing NULL row...");
    $null_row = $sth->fetchrow_arrayref;
    ok ($null_row && !defined($null_row->[1]), "NULL blob row");
}

ok ($sth->finish) if $sth;

ok $dbh->do("DROP TABLE $table"), "Drop table $table";

ok $dbh->disconnect;
