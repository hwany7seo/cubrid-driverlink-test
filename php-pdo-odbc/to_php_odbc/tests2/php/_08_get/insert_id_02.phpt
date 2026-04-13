--TEST--
cubrid_insert_id
--SKIPIF--
<?php 
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, "DROP TABLE if exists insert_tb");
odbc_exec($conn, "CREATE TABLE insert_tb(a int auto_increment, b varchar(10))");

for($i=1;$i<=10;$i++){
   odbc_exec($conn,"insert into insert_tb(b) values($i)");
}
$id1 = cubrid_insert_id();
var_dump($id1);
printf("\n\n");

odbc_exec($conn,"insert into insert_tb values(1,'1')");
$id2 = cubrid_insert_id($conn);
if(FALSE === $id2){
   printf("[002]Return value is false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}elseif(0 === $id2){
   printf("[002]Return value is 0, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[002]id: %s\n",$id2);
}

odbc_exec($conn,"select * from insert_tb");
$id3 = cubrid_insert_id($conn);
if(FALSE === $id3){
   printf("[003]Return value is false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}elseif(0 === $id3){
   printf("[003]Return value is 0, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[003]id: %s\n",$id3);
}

odbc_close($conn);

print "Finishe!\n";
?>
--CLEAN--
--EXPECTF--
string(2) "10"


[002]id: 10
[003]Return value is 0, [0] []
Finishe!
