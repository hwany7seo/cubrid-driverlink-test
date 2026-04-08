--TEST--
ODBC BLOB/CLOB round-trip
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
if (!function_exists('odbc_longreadlen') || !function_exists('odbc_binmode')) {
    die('skip odbc_longreadlen/odbc_binmode not available');
}
if (!defined('ODBC_BINMODE_RETURN')) {
    die('skip ODBC_BINMODE_RETURN not defined');
}
?>
--FILE--
<?php
include 'connect.inc';
require 'table.inc';

$conn = $conn ?? null;
if (!cubrid_odbc_compat_is_link($conn)) {
    printf("[001] No connection after table.inc\n");
    exit(1);
}

cubrid_odbc_set_last_connection($conn);

$blob = "\x89PNG\r\n\x1a\n" . str_repeat("\xFE\x01", 200) . 'cubrid_odbc_lob_basic';
$blob_len = strlen($blob);

$clob = "CLOB ODBC 테스트 — ASCII + \x01\x02 binary-ish + 한국어";

if (!odbc_exec($conn, 'DELETE FROM php_cubrid_test')) {
    printf("[002] DELETE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

$ins = odbc_prepare($conn, 'INSERT INTO php_cubrid_test (d, e, f) VALUES (?, ?, ?)');
if (!$ins) {
    printf("[003] prepare INSERT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

if (!odbc_execute($ins, array('odbc_lob_basic', $blob, $clob))) {
    printf("[004] execute INSERT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

if (!odbc_commit($conn)) {
    printf("[005] commit failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

$sel = odbc_exec($conn, "SELECT e, f FROM php_cubrid_test WHERE d = 'odbc_lob_basic'");
if (!$sel) {
    printf("[006] SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

odbc_longreadlen($sel, 10485760);
/* PASSTHRU(0)는 LONG/BLOB를 stdout으로 보냄 — 변수로 받으려면 RETURN(1) */
odbc_binmode($sel, ODBC_BINMODE_RETURN);

if (!odbc_fetch_row($sel)) {
    printf("[007] No row returned\n");
    odbc_free_result($sel);
    exit(1);
}

$e = odbc_result($sel, 'e');
$f = odbc_result($sel, 'f');
odbc_free_result($sel);

$blob_ok = is_string($e) && strlen($e) === $blob_len && $e === $blob;
$clob_ok = is_string($f) && $f === $clob;

printf("BLOB len=%d ok=%d\n", is_string($e) ? strlen($e) : -1, $blob_ok ? 1 : 0);
printf("CLOB len=%d ok=%d\n", is_string($f) ? strlen($f) : -1, $clob_ok ? 1 : 0);

if (!$blob_ok || !$clob_ok) {
    exit(1);
}

print "done!\n";
?>
--CLEAN--
<?php
require_once('clean_table.inc');
?>
--EXPECTF--
BLOB len=%d ok=1
CLOB len=%d ok=1
done!
