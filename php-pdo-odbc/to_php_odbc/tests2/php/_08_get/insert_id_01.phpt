--TEST--
cubrid_insert_id
--SKIPIF--
<?php # vim:ft=php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, "DROP TABLE if exists insert_id_tb1");
odbc_exec($conn, "DROP TABLE if exists insert_id_tb2");
odbc_exec($conn, "CREATE TABLE insert_id_tb2(a int auto_increment(1,2), b varchar(10))");
odbc_exec($conn, "CREATE TABLE insert_id_tb1 (d int AUTO_INCREMENT(1, 2), e numeric(38, 0) AUTO_INCREMENT(11111111111111111111111111111111111111, 2), t varchar(20))");

printf("#####positive example#####\n");
//no insert data to table
$id = cubrid_insert_id($conn);
if(FALSE == $id){
   printf("[001]Expect false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[001]id: %s\n",$id);
}

for($i=0;$i<10;$i++){
   odbc_exec($conn,"insert into insert_id_tb2(b) values($i)");
}
$id1 = cubrid_insert_id();
var_dump($id1);

//get insert_id again
$id2 = cubrid_insert_id();
printf("id2: %s\n",$id2);

//passing $conn_identifier parameter
$id6 = cubrid_insert_id($conn);
if(FALSE == $id6){
   printf("[002]Return value is false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}elseif(0 == $id6){
   printf("[002]Return value is 0, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[002]id: %s\n",$id6);
}


printf("\n\n#####negative example#####\n");
//table has two auto_increment
for($i=0;$i<10;$i++){
   odbc_exec($conn,"insert into insert_id_tb1(t) values($i)");
}
$id4= cubrid_insert_id();
printf("id4: %s\n",$id4);


//query statement is not insert
odbc_exec($conn,"select * from insert_id_tb2");
$id5 = cubrid_insert_id($conn);
if(FALSE == $id5){
   printf("[003]Return value is false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}elseif(0 == $id5){
   printf("[003]Return value is 0, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[003] id: %s\n",$id5);
}

$id6 = cubrid_insert_id("nothisparameter");
if(FALSE == $id6){
   printf("[004]Return value is false, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}elseif(0 == $id6){
   printf("[004]Return value is 0, [%d] [%s]\n",odbc_error($conn),odbc_errormsg($conn));
}else{
   printf("[004] id: %s\n",$id6);
}


odbc_close($conn);

print "Finishe!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

Warning: Error: CLIENT, -30002, Invalid API call in %s on line %d
[001]Expect false, [-30002] [Invalid API call]
string(2) "19"
id2: 19
[002]id: 19


#####negative example#####
id4: 19
[003]Return value is false, [0] []

Warning: cubrid_insert_id() expects parameter 1 to be resource, string given in %s on line %d
[004]Return value is false, [0] []
Finishe!
