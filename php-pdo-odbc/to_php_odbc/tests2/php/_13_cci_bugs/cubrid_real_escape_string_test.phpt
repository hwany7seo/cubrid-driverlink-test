--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
odbc_exec($conn, "DROP TABLE IF EXISTS cubrid_test");
odbc_exec($conn, "CREATE TABLE cubrid_test (id int, t varchar(20))");

$unescaped1='\\';
$escaped1=cubrid_real_escape_string($unescaped1,$conn);
odbc_exec($conn, "INSERT INTO cubrid_test (id,t) VALUES(1,'$escaped1')");
$req1 = odbc_exec($conn, "SELECT * FROM cubrid_test where id=1 ");
while($row = odbc_fetch_array($req1)){
   var_dump($row);
}
odbc_free_result($req1);

$unescaped2="\\";
odbc_exec($conn, "INSERT INTO cubrid_test (id,t) VALUES(2,'$unescaped2')");
$req2 = odbc_exec($conn, "SELECT * FROM cubrid_test where id=2 ");
while($row = odbc_fetch_array($req2)){
   var_dump($row);
}
odbc_free_result($req2);
odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["id"]=>
  string(1) "1"
  ["t"]=>
  string(1) "\"
}
array(2) {
  ["id"]=>
  string(1) "2"
  ["t"]=>
  string(1) "\"
}
Finished!
