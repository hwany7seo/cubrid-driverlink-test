#!perl -w
#
# The base driver test of DBD::ODBC
#

use Test::More tests => 6;

use vars qw($mdriver);
use lib 't', '.';
require 'lib.pl';

BEGIN {
    use_ok('DBI') or BAIL_OUT "Unable to load DBI";
    use_ok('DBD::ODBC') or BAIL_OUT "Unable to load DBD::ODBC"; # Changed to DBD::ODBC
}

$switch = DBI->internal;
cmp_ok ref $switch, 'eq', 'DBI::dr', 'Internal set';

# This is a special case. install_driver should not normally be used.
$drh= DBI->install_driver($mdriver);

ok $drh, 'Install driver';

cmp_ok ref $drh, 'eq', 'DBI::dr', 'DBI::dr set';

ok $drh->{Version}, "Version $drh->{Version}"; 
