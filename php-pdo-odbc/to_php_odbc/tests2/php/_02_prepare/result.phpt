--TEST--
cubrid_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS result_tb');
$sql ="CREATE TABLE result_tb(id int, name varchar(10), address string default NULL, phone char(10))";
odbc_exec($conn,$sql);
odbc_exec($conn,"insert into result_tb values(1,'name1','string1','1111-11-11'),(2,'name2','string2','2222-22-22'),(3,'name3','string3','3333-33-33'),(4,'name4','string4','4444-44-44'),(5,'name5','string5','5555-55-55')");
odbc_exec($conn,"insert into result_tb(id,name) values(6,'name6')");

$req = odbc_exec($conn, "SELECT * FROM result_tb where id<=5");
printf("#####positive testing#####\n");
$row_num = odbc_num_rows($req);
$col_num=odbc_num_fields($req);
for($i=0;$i<$row_num-2;$i++){
   printf("the %d row value: \n",$i);
   for($j=0;$j<$col_num;$j++){
      $result=cubrid_result($req,$i,$j);
      var_dump($result);
   }
   printf("\n\n");
}
$result = cubrid_result($req, 0);
var_dump($result);
$result = cubrid_result($req, 4);
var_dump($result);

//offset is column name
$result = cubrid_result($req, 2,'address');
var_dump($result);
odbc_free_result($req);

printf("column name has been aliased\n");
$req2 = odbc_exec($conn, "SELECT id as a, name as b, address as c, phone as d FROM result_tb where id<=5 order by id desc");
$row_num = odbc_num_rows($req2);
$col_num=odbc_num_fields($req2);
$array=array('a','b','c','d');
for($i=0;$i<$row_num-2;$i++){
   printf("the %d row value: \n",$i);
   for($j=0;$j<$col_num;$j++){
      $result=cubrid_result($req2,$i,$array[$j]);
      var_dump($result);
   }
   printf("\n\n");
}
odbc_free_result($req2);

print("\n#####query reslut is null#####\n");
$req5=odbc_exec($conn, "SELECT * FROM result_tb where id=6");
$result = cubrid_result($req5,0,'address');
if(FALSE == $result){
   printf("No expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(is_null($rlt7)){
   printf("Expect null [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("Get result success\n");
   var_dump($result);
}
$result = cubrid_result($req5,0);
var_dump($result);
odbc_free_result($req5);


printf("#####negative testing#####\n");
//$row < 0
$req3 = odbc_exec($conn, "SELECT * FROM result_tb ");
$rlt1=cubrid_result($req3,-1);
if(FALSE == $rlt1){
   printf("[001]Expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[001]Get result success\n");
   var_dump($rlt1);
}

//row value is large than range
$rlt2=cubrid_result($req3,5);
if(FALSE == $rlt2){
   printf("[002]Expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[002]Get result success\n");
   var_dump($rlt2);
}

//offset is less than 0
$rlt3=cubrid_result($req3,0,-1);
if(FALSE == $rlt3){
   printf("[003]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[003]Get result success\n");
   var_dump($rlt3);
}

//offset is large than range
$rlt4=cubrid_result($req3,1,4);
if(FALSE == $rlt4){
   printf("[004]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[004]Get result success\n");
   var_dump($rlt4);
}

//no this offset 
$rlt5=cubrid_result($req3,2,"nothisoffset");
if(FALSE == $rlt5){
   printf("[005]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[005]Get result success\n");
   var_dump($rlt5);
}

//correct result
$rlt6=cubrid_result($req3,2,2);
if(FALSE == $rlt6){
   printf("[006]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[006]Get result success\n");
   var_dump($rlt6);
}
odbc_free_result($req3);

printf("\n\n#####query result is no data#####\n");
$req4 = odbc_exec($conn, "SELECT * FROM result_tb where id > 10");
$rlt7=cubrid_result($req4, 0);
if(FALSE == $rlt7){
   printf("[007]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[007]Get result success\n");
   var_dump($rlt7);
}

$rlt8=cubrid_result($req4, 0,1);
if(FALSE == $rlt8){
   printf("[008]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[008]Get result success\n");
   var_dump($rlt8);
}

odbc_free_result($req4);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive testing#####
the 0 row value: 
string(1) "1"
string(5) "name1"
string(7) "string1"
string(10) "1111-11-11"


the 1 row value: 
string(1) "2"
string(5) "name2"
string(7) "string2"
string(10) "2222-22-22"


the 2 row value: 
string(1) "3"
string(5) "name3"
string(7) "string3"
string(10) "3333-33-33"


string(1) "1"
string(1) "5"
string(7) "string3"
column name has been aliased
the 0 row value: 
string(1) "5"
string(5) "name5"
string(7) "string5"
string(10) "5555-55-55"


the 1 row value: 
string(1) "4"
string(5) "name4"
string(7) "string4"
string(10) "4444-44-44"


the 2 row value: 
string(1) "3"
string(5) "name3"
string(7) "string3"
string(10) "3333-33-33"



#####query reslut is null#####
No expect false [0] []
string(1) "6"
#####negative testing#####
[001]Expect false [0] []
[002]Get result success
string(1) "6"

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[003]Expect false [-20013] [Column index is out of range]

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[004]Expect false [-20013] [Column index is out of range]

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[005]Expect false [-20013] [Column index is out of range]
[006]Get result success
string(7) "string3"


#####query result is no data#####
[007]Expect false [0] []
[008]Expect false [0] []
Finished!
