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

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

@odbc_exec($conn, "DROP TABLE IF EXISTS roll_tb");
cubrid_query('CREATE TABLE roll_tb(a int)');
cubrid_query('INSERT INTO roll_tb(a) VALUE(1)');

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = cubrid_query('SELECT * FROM roll_tb');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_query('DROP TABLE IF EXISTS roll_tb');

odbc_rollback($conn);

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

$req = cubrid_query('SELECT * FROM roll_tb');
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
