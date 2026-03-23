--TEST--
cubrid_move_cursor
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS move_tb');
$sql ="CREATE TABLE move_tb(id int, name varchar(10))";
odbc_exec($conn,$sql);
odbc_exec($conn,"insert into move_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

$req = odbc_exec($conn, "SELECT * FROM move_tb");
printf("#####positive testing#####\n");
$result = odbc_fetch_row($req);
var_dump($result);

cubrid_move_cursor($req, 2, CUBRID_CURSOR_FIRST);
$result = odbc_fetch_row($req);
var_dump($result);

cubrid_move_cursor($req, 1, CUBRID_CURSOR_CURRENT);
$result = odbc_fetch_row($req);
var_dump($result);

cubrid_move_cursor($req, 0, CUBRID_CURSOR_CURRENT);
$result = odbc_fetch_row($req);
var_dump($result);

odbc_free_result($req);

printf("#####negative testing#####\n");
//origin value is numeric
$req1 = odbc_exec($conn, "SELECT * FROM move_tb");
$mov1=cubrid_move_cursor($req1, 1,1);
if(FALSE == $mov1){
   printf("[001]Expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[001]Move success\n");
   $result = odbc_fetch_row($req1);
   var_dump($result);
}

//offset is large than range
$mov2=cubrid_move_cursor($req1,7, CUBRID_CURSOR_FIRST);
if(FALSE == $mov2){
   printf("[002]Expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[002]Move success\n");
   $result = odbc_fetch_row($req1);
   var_dump($result);
}

//offset is less than 0
$mov3=cubrid_move_cursor($req1,-1, CUBRID_CURSOR_FIRST);
if(FALSE == $mov3){
   printf("[003]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[003]Move success\n");
   $result = odbc_fetch_row($req1);
   var_dump($result);
}
odbc_free_result($req1);

//query result is no data
$req2 = odbc_exec($conn, "SELECT * FROM move_tb where id > 10");
$mov4=cubrid_move_cursor($req2, 1, CUBRID_CURSOR_FIRST);
if(FALSE == $mov4){
   printf("[004]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[004]Move success\n");
   $result = odbc_fetch_row($req2);
   var_dump($result);
}
odbc_free_result($req2);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive testing#####
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(5) "name1"
}
array(2) {
  [0]=>
  string(1) "2"
  [1]=>
  string(5) "name2"
}
array(2) {
  [0]=>
  string(1) "4"
  [1]=>
  string(5) "name4"
}
array(2) {
  [0]=>
  string(1) "5"
  [1]=>
  string(5) "name5"
}
#####negative testing#####
[001]Move success
array(2) {
  [0]=>
  string(1) "2"
  [1]=>
  string(5) "name2"
}

Warning: Error: CCI, -20005, Invalid cursor position in %s on line %d
[002]Expect false [-20005] [Invalid cursor position]

Warning: Error: CCI, -20005, Invalid cursor position in %s on line %d
[003]Expect false [-20005] [Invalid cursor position]

Warning: Error: CCI, -20005, Invalid cursor position in %s on line %d
[004]Expect false [-20005] [Invalid cursor position]
Finished!
