--TEST--
odbc_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

@odbc_exec($conn, "DROP TABLE IF EXISTS rollback_test");
cubrid_query('CREATE TABLE rollback_test(a int)', $conn);
cubrid_query('INSERT INTO rollback_test(a) VALUES(1)', $conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = cubrid_query('SELECT * FROM rollback_test', $conn);
$res = cubrid_fetch_assoc($req);

var_dump($res);

cubrid_query('DROP TABLE IF EXISTS rollback_test', $conn);

odbc_rollback($conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

$req = cubrid_query('SELECT * FROM rollback_test', $conn);
$res = cubrid_fetch_assoc($req);

var_dump($res);

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(1) {
  ["a"]=>
  string(1) "1"
}
array(1) {
  ["a"]=>
  string(1) "1"
}
done!
