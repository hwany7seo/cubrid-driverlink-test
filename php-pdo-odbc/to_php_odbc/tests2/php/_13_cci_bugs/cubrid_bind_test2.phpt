--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only API — Not supported by PHP ODBC (non-standard bind)");
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, 'DROP TABLE IF EXISTS time2_tb');

odbc_exec($conn,"CREATE TABLE time2_tb(c1 int, c2 time, c3 date, c4 TIMESTAMP);");
$req2 = odbc_prepare($conn, "INSERT INTO time2_tb VALUES(1,?,?,?);");
if(false == ($tmp=cubrid_bind($req2, 1, '25:22:60','time'))){
   printf("bind '25:22:60','time' failed \n");
}else{
   printf("bind time success\n");
}

//cubrid_bind($req2, 1, '02:22:59','time');

cubrid_bind($req2, 2, '2012-03-02');
cubrid_bind($req2, 3, '08/14/1977 12:36:10 pm');
cubrid_execute($req2);

$req3= odbc_exec($conn, "SELECT * FROM time2_tb");
if($req3){
   $result = odbc_fetch_array($req3);
   var_dump($result);
   odbc_free_result($req3);
}

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
bind time success

Warning: Error: DBMS, -787, Conversion error in time format.%s. in %s on line %d
bool(false)
Finished!
