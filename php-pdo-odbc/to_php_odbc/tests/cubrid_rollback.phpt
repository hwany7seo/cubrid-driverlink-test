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
@odbc_exec($conn, "DROP TABLE IF EXISTS rollback_test");
odbc_exec($conn, 'CREATE TABLE rollback_test(a int)');
odbc_exec($conn, 'INSERT INTO rollback_test(a) VALUES(1)');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = odbc_exec($conn, 'SELECT * FROM rollback_test');
$res = cubrid_fetch_assoc($req);

var_dump($res);

odbc_exec($conn, 'DROP TABLE IF EXISTS rollback_test');

odbc_rollback($conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
$req = odbc_exec($conn, 'SELECT * FROM rollback_test');
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
