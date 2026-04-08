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
cubrid_odbc_set_last_connection($conn);

if (cubrid_get_autocommit($conn)) {
	printf("Autocommit is ON.\n");
} else {
	printf("Autocommit is OFF.");
}

@odbc_exec($conn, "DROP TABLE IF EXISTS autocommit_test");
cubrid_query('CREATE TABLE autocommit_test(a int)', $conn);
cubrid_query('INSERT INTO autocommit_test(a) VALUES(1)', $conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

$req = cubrid_query('SELECT * FROM autocommit_test', $conn);
$res = cubrid_fetch_assoc($req);

var_dump($res);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
cubrid_query('UPDATE autocommit_test SET a=2', $conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

$req = cubrid_query('SELECT * FROM autocommit_test', $conn);
$res = cubrid_fetch_assoc($req);

var_dump($res);

cubrid_query('DROP TABLE IF EXISTS autocommit_test', $conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

$req = cubrid_query('SELECT * FROM autocommit_test', $conn);

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
