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
# get the full path of the hotcopy_script.
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
    # Adjusted tests count
    plan tests => 15;
}

ok $dbh->do("DROP TABLE IF EXISTS $table"), "Drop table if exists $table";

my $create = <<EOT;
CREATE TABLE $table (
    id INT NOT NULL DEFAULT 0,
    picture BLOB )
EOT

ok ($dbh->do($create));

my ($sth, $query);

# Insert a row into the test table .......
$query = "INSERT INTO $table VALUES(1, ?)";
ok ($sth = $dbh->prepare($query));

# cubrid_lob_import is CUBRID specific. 
# For ODBC, we should read file and bind it as binary.
# Or just skip this part if it relies on driver specific method.
# Let's try to simulate basic BLOB insert via parameter.

my $test_png_file = File::Spec->catfile($volume, $script_directory, "cubrid_logo.png");
# Check if file exists, if not use a dummy
if (! -e $test_png_file) {
    # try looking in parent dir or ../cubrid-perl/t
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

# Workaround: HY021 with SQL_BLOB — use LONGVARBINARY (ODBC common for byte LOB input).
ok ($sth->bind_param(1, $blob_data, DBI::SQL_LONGVARBINARY), "bind blob data");
ok ($sth->execute);

# Insert a NULL row into the test table ......
$query = "INSERT INTO $table VALUES(2, ?)";
ok ($sth = $dbh->prepare($query));
# $sth->cubrid_lob_import(1, NULL, DBI::SQL_BLOB);
ok ($sth->bind_param(1, undef));
ok ($sth->execute);

ok ($sth->finish);

# Now, try SELECT'ing the first row out.
ok ($sth = $dbh->prepare("SELECT * FROM $table WHERE id = 1"), "prepare to select picture");
ok ($sth->execute, "executing...");

# cubrid_lob_get/export are CUBRID specific.
# LongReadLen/LongTruncOk must be set before prepare for some ODBC drivers
my $row = $sth->fetchrow_arrayref;
# ok ($sth->cubrid_lob_get(2), 'get lob object');
# ok ($sth->cubrid_lob_export(1, "out"), 'export lob object');

ok ($row && $row->[1], "Got blob data");

# Now try SELECT'ing the second row out: NULL
# ...

# Cleanup
ok ($sth->finish);

ok $dbh->do("DROP TABLE $table"), "Drop table $table";

ok $dbh->disconnect;
