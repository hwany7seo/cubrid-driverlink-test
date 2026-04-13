--TEST--
cubrid_result for APIS-129
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

odbc_exec($conn, 'DROP TABLE IF EXISTS tb');
odbc_exec($conn,"CREATE TABLE tb(id int, name varchar(10), address string default NULL, phone varchar(10))");
odbc_exec($conn,"insert into tb(id,name,phone) values(6,'name6','NULL')");

$req=odbc_exec($conn, "SELECT * FROM tb ");
$value=cubrid_result($req,0,'address');
if(is_null($value)){
   printf("[001]Expect null [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(FALSE == $value ){
   printf("[001] No expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[001]Get result success\n");
   var_dump($value);
}

$value2=cubrid_result($req,0,2);
if(is_null($value2)){
   printf("[001]Expect null [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(FALSE == $value2 ){
   printf("[001] No expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[001]Get result success\n");
   var_dump($value2);
}


printf("#####correct resutl:#####\n");
$result2 = cubrid_result($req,0,1);
var_dump($result2);

$result3 = cubrid_result($req,0,3);
if(FALSE == $result3){
   printf("[002]No expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(is_null($result3)){
   printf("[002]Expect null [%d] [%s]\n", odbc_error(), odbc_errormsg());
}else{
   printf("[002]Get result success\n");
   var_dump($result3);
}
odbc_free_result($req);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
[001]Expect null [0] []
[001]Expect null [0] []
#####correct resutl:#####
string(5) "name6"
[002]Get result success
string(4) "NULL"
Finished!
