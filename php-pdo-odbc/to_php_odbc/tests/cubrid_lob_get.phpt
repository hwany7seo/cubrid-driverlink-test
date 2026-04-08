--TEST--
cubrid_lob_get
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
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

$cubrid_retval = odbc_execute($cubrid_req, 1, $fp, "blob");
if (!$cubrid_retval) {
    printf("[002] Can't bind blob type parameter. [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}

$cubrid_retval = odbc_exec($cubrid_req);
if (!$cubrid_retval) {
    printf("[003] Blob data insertion failed. [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}

$tmp = cubrid_lob_get($cubrid_conn);
if ($tmp !== NULL) {
    printf("[004] Expecting NULL got %s/%s\n", gettype($tmp), $tmp);
    exit(1);
}

$tmp = cubrid_lob_get($cubrid_conn, NULL);
if ($tmp !== false) {
    printf("[005] Expecting boolean/false got %s/%s\n", gettype($tmp), $tmp);
    exit(1);
}

$tmp = cubrid_lob_get($cubrid_conn, "insert into php_cubrid_test(a) values (1)");
if ($tmp !== false) {
    printf("[006] Expecting boolean/false got %s/%s\n", gettype($tmp), $tmp);
    exit(1);
}

$tmp = cubrid_lob_get($cubrid_conn, "select a from php_cubrid_test");
if ($tmp !== false) {
    printf("[007] Expecting boolean/false got %s/%s\n", gettype($tmp), $tmp);
    exit(1);
}

$tmp = cubrid_lob_get($cubrid_conn, "select a,e from php_cubrid_test");
if ($tmp !== false) {
    printf("[008] Expecting boolean/false got %s/%s\n", gettype($tmp), $tmp);
    exit(1);
}

$lobs = cubrid_lob_get($cubrid_conn, "select e from php_cubrid_test");

if (cubrid_lob_size($lobs[0]) != filesize("./cubrid_logo.png")) {
    printf("[006] Blob data export error.\n");
    exit(1);
}

cubrid_lob_close($lobs);

odbc_commit($cubrid_conn);
cubrid_disconnect($cubrid_conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--

Warning: cubrid_lob_get() expects exactly 2 parameters, 1 given in %s on line %d

Warning: Error: DBMS, -424, No statement to execute.%s in %s on line %d

Warning: cubrid_lob_get(): Get result info fail or sql type is not select in %s on line %d

Warning: cubrid_lob_get(): Column type is not BLOB or CLOB. in %s on line %d

Warning: cubrid_lob_get(): More than one columns returned in %s on line %d
done!
