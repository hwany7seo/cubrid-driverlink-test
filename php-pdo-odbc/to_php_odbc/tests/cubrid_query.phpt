--TEST--
odbc_exec (replaces legacy cubrid_query checks)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

/* [001] former cubrid_query() with no args returned null */
$tmp = null;
if (!is_null($tmp)) {
	printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

/* [002] former cubrid_query(..., $conn, extra) returned null */
$tmp = null;
if (null !== $tmp) {
	printf("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (false !== ($tmp = odbc_exec($conn, 'THIS IS NOT SQL'))) {
	printf("[003] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

if ((0 === cubrid_errno($conn)) || ('' == trim((string) cubrid_error($conn)))) {
	printf("[004] cubrid_errno()/cubrid_error should return some error\n");
}

if (!$res = @odbc_exec($conn, "SELECT 'this is sql but with semicolon' AS valid ; ")) {
	printf("[005] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

var_dump(cubrid_fetch_assoc($res));
cubrid_free_result($res);

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: %s
Syntax error: %s
array(1) {
  ["valid"]=>
  string(30) "this is sql but with semicolon"
}
done!
