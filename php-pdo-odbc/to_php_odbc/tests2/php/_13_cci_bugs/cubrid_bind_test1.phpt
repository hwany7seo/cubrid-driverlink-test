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
odbc_exec($conn, 'DROP TABLE IF EXISTS time_tb');
$sql = <<<EOD
CREATE TABLE time_tb(c1 string, time_tb time,date_t date);
EOD;
odbc_exec($conn,$sql);

//date time type
$req = odbc_prepare($conn, "INSERT INTO time_tb VALUES('time date test',?,?);");
cubrid_bind($req, 1, '02:22:22','time');
cubrid_bind($req, 2, '1977/08/14','date');
odbc_execute($req);

$req2= odbc_exec($conn, "SELECT * FROM time_tb where c1 like 'time%';");
if($req2){
   $result = odbc_fetch_array($req2);
   var_dump($result);
}
odbc_free_result($req2);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(3) {
  ["c1"]=>
  string(14) "time date test"
  ["time_tb"]=>
  string(8) "02:22:22"
  ["date_t"]=>
  string(10) "1977-08-14"
}
Finished!
