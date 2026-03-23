--TEST--
cubrid_data_seek for APIS-132
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS seek_tb');
$sql ="CREATE TABLE seek_tb(id int, name varchar(10))";
odbc_exec($conn,$sql);
odbc_exec($conn,"insert into seek_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

printf("#####negative testing#####\n");
$req1 = odbc_exec($conn, "SELECT * FROM seek_tb");

//offset is large than range
$mov2=cubrid_data_seek($req1,5);
if(FALSE == $mov2){
   printf("[002]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[002]Move success\n");
   $result = odbc_fetch_row($req1);
   var_dump($result);
}

//offset is less than 0
$mov3=cubrid_data_seek($req1,-1);
if(FALSE == $mov3){
   printf("[003]Expect false [%d] [%s]\n",odbc_error(), odbc_errormsg());
}else{
   printf("[003]Move success\n");
   $result = odbc_fetch_row($req1);
   var_dump($result);
}
odbc_free_result($req1);


odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative testing#####

Warning: Error: CCI, -20005, Invalid cursor position in %s on line %d
[002]Expect false [-20005] [Invalid cursor position]

Warning: Error: CCI, -20005, Invalid cursor position in %s on line %d
[003]Expect false [-20005] [Invalid cursor position]
Finished!
