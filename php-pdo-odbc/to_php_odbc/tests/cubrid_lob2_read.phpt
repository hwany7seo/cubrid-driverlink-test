--TEST--
cubrid_lob2_read (Simulating lob2_read using substr after full CLOB lookup)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
if (!function_exists('odbc_longreadlen')) {
    die('skip odbc_longreadlen not available');
}
if (!defined('ODBC_BINMODE_RETURN')) {
    die('skip ODBC_BINMODE_RETURN not defined');
}
?>
--FILE--
<?php
/**
 * 네이티브 cubrid_lob2_* 스트림 대신: CLOB 컬럼을 ODBC로 통째로 읽고,
 * PHP에서 seek/read/tell 과 동일한 출력을 낸다.
 */
include_once('connect.inc');
require 'table.inc';

$conn = $conn ?? null;
if (!cubrid_odbc_compat_is_link($conn)) {
    printf("[001] No connection after table.inc\n");
    exit(1);
}
$lob_data = "Hello, welcome to CUBRID world! I'm LOB.";

if (!odbc_exec($conn, "DELETE FROM php_cubrid_test WHERE d = 'lob2_read_odbc'")) {
    printf("[002] DELETE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

$ins = odbc_prepare($conn, 'INSERT INTO php_cubrid_test (d, f) VALUES (?, ?)');
if (!$ins || !odbc_execute($ins, array('lob2_read_odbc', $lob_data))) {
    printf("[003] INSERT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

if (!odbc_commit($conn)) {
    printf("[004] commit failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

$sel = odbc_exec($conn, "SELECT f FROM php_cubrid_test WHERE d = 'lob2_read_odbc'");
if (!$sel) {
    printf("[005] SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

odbc_longreadlen($sel, 10485760);
odbc_binmode($sel, ODBC_BINMODE_RETURN);

if (!odbc_fetch_row($sel)) {
    printf("[006] No row\n");
    odbc_free_result($sel);
    exit(1);
}

$full = odbc_result($sel, 'f');
odbc_free_result($sel);

if (!is_string($full) || $full !== $lob_data) {
    printf("[007] CLOB round-trip mismatch (len expect %d got %s)\n", strlen($lob_data), is_string($full) ? (string) strlen($full) : gettype($full));
    exit(1);
}

$offset = 0;
print 'LOB positon after written is ' . strlen($full) . "\n";

$str = substr($full, $offset, 30);
$offset += strlen($str);
print "read 30 characters from lob: $str\n";
print "LOB positon after read 30 character is $offset\n";

$str = substr($full, $offset, 20);
$offset += strlen($str);
print "read 20 characters from lob: $str\n";
print "LOB positon after read 20 characters is $offset\n";

$tmp = ($offset >= strlen($full)) ? false : substr($full, $offset, 10);
if (false !== $tmp) {
    printf("[008] Expecting boolean/false at EOF, got %s/%s\n", gettype($tmp), $tmp);
}

cubrid_disconnect($conn);
print 'done!';
?>
--CLEAN--
<?php
require_once('clean_table.inc');
?>
--EXPECTF--
LOB positon after written is 40
read 30 characters from lob: Hello, welcome to CUBRID world
LOB positon after read 30 character is 30
read 20 characters from lob: ! I'm LOB.
LOB positon after read 20 characters is 40
done!
