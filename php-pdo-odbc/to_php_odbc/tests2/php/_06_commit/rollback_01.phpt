--TEST--
cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

@odbc_exec($conn, "DROP TABLE IF EXISTS roll_tb");
odbc_exec($conn, 'CREATE TABLE roll_tb(a int)');
odbc_exec($conn, 'INSERT INTO roll_tb(a) VALUE(1)');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = odbc_exec($conn, 'SELECT * FROM roll_tb');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

odbc_exec($conn, 'DROP TABLE IF EXISTS roll_tb');

odbc_rollback($conn);

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$req = odbc_exec($conn, 'SELECT * FROM roll_tb');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

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
