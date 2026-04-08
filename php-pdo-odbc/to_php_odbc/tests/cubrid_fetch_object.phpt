--TEST--
odbc_fetch_object
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
/**
 * PHP 8.4 ext/odbc 의 odbc_fetch_object() 는 (statement, ?int $row) 만 지원한다.
 * mysqli/cubrid-php 스타일의 (stmt, className, ctorArgs) 시그니처는 없다.
 */
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

if (!($res = odbc_exec($conn, "SELECT * FROM code LIMIT 5"))) {
	printf('[003] [%s] %s\n', odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

var_dump(odbc_fetch_object($res));
var_dump(odbc_fetch_object($res));
var_dump(odbc_fetch_object($res));

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
object(stdClass)#%d (2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
object(stdClass)#%d (2) {
  ["s_name"]=>
  string(1) "W"
  ["f_name"]=>
  string(5) "Woman"
}
object(stdClass)#%d (2) {
  ["s_name"]=>
  string(1) "M"
  ["f_name"]=>
  string(3) "Man"
}
done!
