--TEST--
cubrid_unbuffered_query cubrid_fress_result
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
odbc_exec($conn, 'DROP TABLE IF EXISTS unbuffered_tb');
odbc_exec($conn,"CREATE TABLE unbuffered_tb(id int primary key, name varchar(10))");
odbc_exec($conn,"insert into unbuffered_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

printf("#####positive example#####\n");
//select statement
$unbuff=cubrid_unbuffered_query("select * from unbuffered_tb",$conn);
$result=odbc_fetch_array($unbuff);
var_dump($result);
odbc_free_result($unbuff);

$unbuff2=cubrid_unbuffered_query("select * from unbuffered_tb where id >3");
$result2=odbc_fetch_array($unbuff2);
var_dump($result2);
odbc_free_result($unbuff2);

//show statement
odbc_exec($conn, "drop table if EXISTS unbuffer2");
odbc_exec($conn, "CREATE TABLE unbuffer2(id INT, phone VARCHAR(10),address string,email char(30),coment string );");
odbc_exec($conn, "create index index1 on unbuffer2(id)");
odbc_exec($conn, "create reverse unique index reverse_unique_index on unbuffer2(phone)");
odbc_exec($conn, "create reverse index reverse_index on unbuffer2(address)");
odbc_exec($conn, "create unique index unique_index on unbuffer2(email)");
//odbc_exec($conn, "");
//odbc_exec($conn, "");
$unbuff3=cubrid_unbuffered_query("show index in unbuffer2;");
$result3=odbc_fetch_array($unbuff3);
odbc_free_result($unbuff3);

//describe
$unbuff4=cubrid_unbuffered_query("describe unbuffer2;");
if (FALSE == $unbuff4) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    $row=odbc_fetch_array($unbuff4);
    var_dump($row);
}
odbc_free_result($unbuff4);

$unbuff5=cubrid_unbuffered_query("explain unbuffered_tb;");
$result5=odbc_fetch_array($unbuff5);
var_dump($result5);
odbc_free_result($unbuff5);

$unbuff6=cubrid_unbuffered_query("insert into unbuffered_tb values(7,'name7');",$conn);
if (FALSE == $unbuff6) {
    printf("[002]No expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    printf("[002]Insert success. [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$unbuff7=cubrid_unbuffered_query("delete from unbuffered_tb where id =1");
if (FALSE == $unbuff7) {
    printf("[003]No expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    printf("[003]Delete success. [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$unbuff8=cubrid_unbuffered_query("drop table if exists unbuffered_tb");
if (FALSE == $unbuff8) {
    printf("[004]No expect false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    printf("[004]Drop success. [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(5) "name1"
}
array(2) {
  ["id"]=>
  string(1) "4"
  ["name"]=>
  string(5) "name4"
}
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "MUL"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(2) "NO"
  ["Key"]=>
  string(3) "PRI"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
[002]Insert success. [0] []
[003]Delete success. [0] []
[004]Drop success. [0] []
Finished!
