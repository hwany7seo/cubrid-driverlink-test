--TEST--
cubrid_autocommit
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
if (cubrid_get_autocommit($conn)) {
	printf("Autocommit is ON.\n");
} else {
	printf("Autocommit is OFF.");
}

@odbc_exec($conn, "DROP TABLE IF EXISTS autocommit_test");
odbc_exec($conn, 'CREATE TABLE autocommit_test(a int)');
odbc_exec($conn, 'INSERT INTO autocommit_test(a) VALUES(1)');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
$req = odbc_exec($conn, 'SELECT * FROM autocommit_test');
$res = odbc_fetch_array($req);

var_dump($res);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
odbc_exec($conn, 'UPDATE autocommit_test SET a=2');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
$req = odbc_exec($conn, 'SELECT * FROM autocommit_test');
$res = odbc_fetch_array($req);

var_dump($res);

odbc_exec($conn, 'DROP TABLE IF EXISTS autocommit_test');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
$req = odbc_exec($conn, 'SELECT * FROM autocommit_test');

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
array(1) {
  ["a"]=>
  string(1) "1"
}
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: %s
done!
