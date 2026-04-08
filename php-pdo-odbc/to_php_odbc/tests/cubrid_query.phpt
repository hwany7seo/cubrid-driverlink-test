--TEST--
cubrid_query
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
cubrid_odbc_set_last_connection($conn);

if (!is_null($tmp = @cubrid_query())) {
	printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (NULL !== ($tmp = @cubrid_query("SELECT 1 AS a", $conn, "code"))) {
	printf("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (false !== ($tmp = cubrid_query('THIS IS NOT SQL', $conn))) {
	printf("[003] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

if ((0 === cubrid_errno($conn)) || ('' == trim((string) cubrid_error($conn)))) {
	printf("[004] cubrid_errno()/cubrid_error should return some error\n");
}

if (!$res = cubrid_query("SELECT 'this is sql but with semicolon' AS valid ; ", $conn)) {
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
