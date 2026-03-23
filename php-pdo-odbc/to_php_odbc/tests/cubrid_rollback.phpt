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

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

@odbc_exec($conn, "DROP TABLE IF EXISTS rollback_test");
cubrid_query('CREATE TABLE rollback_test(a int)');
cubrid_query('INSERT INTO rollback_test(a) VALUE(1)');

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = cubrid_query('SELECT * FROM rollback_test');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_query('DROP TABLE IF EXISTS rollback_test');

odbc_rollback($conn);

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

$req = cubrid_query('SELECT * FROM rollback_test');
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
