--TEST--
cubrid_lob_write
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";

require('table.inc');

$tmp = NULL;

$fp = fopen('./cubrid_logo.png', 'rb');

$cubrid_conn = odbc_connect($cubrid_odbc_dsn, "", "");
$cubrid_req = odbc_prepare($cubrid_conn, "insert into php_cubrid_test (e) values (?)");
if (!$cubrid_req) {
    printf("[001] Sql preparation failed. [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}

$cubrid_retval = odbc_execute($cubrid_req, array($fp));
if (!$cubrid_retval) {
    printf("[002] Can't bind blob type parameter. [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}

odbc_commit($cubrid_conn);
cubrid_disconnect($cubrid_conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
done!
