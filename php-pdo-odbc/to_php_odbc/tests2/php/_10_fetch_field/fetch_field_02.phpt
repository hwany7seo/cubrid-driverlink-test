--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--XFAIL--
Fatal error: Allowed memory size, odbc_get_desc_field issue (short -> long type), allow
And issue length in odbc_field_len (SQL_COLUMN_PRECISION) and not support type in odbc_field_type (JSON, ENUM ...)
--FILE--
<?php
//table contains all kind of types 
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn,"drop table if exists field2_tb;");
odbc_exec($conn,"CREATE TABLE field2_tb(c1 string primary key, c2 char(20) not null, c3 int default -2147483648 unique key);");
odbc_exec($conn,"insert into field2_tb values('string111111','char11111',1)");


print("#####negative example#####\n");
$result=odbc_exec($conn,"select * from field2_tb ");
var_dump(odbc_fetch_row($result) );
$field = cubrid_fetch_field($result);
var_dump($field);

printf("#####index < 0#####\n");
$field = cubrid_fetch_field($result,-1);
if(FALSE == $field ){
   printf("Expect false value, [%s] [%s]\n",odbc_error(),odbc_errormsg());   
}else{
   var_dump($field);
}
printf("#####index > range#####\n");
$field = cubrid_fetch_field($result,3);
if(FALSE == $field ){
   printf("Expect false value, [%s] [%s]\n",odbc_error(),odbc_errormsg());
}else{
   var_dump($field);
}
odbc_free_result($result);

//insert result
printf("#####insert result#####\n");
$result1=odbc_exec($conn,"insert into field2_tb(c1,c2,c3) values('insert string','insert char',2)");
$field1 = cubrid_fetch_field($result1);
if(FALSE == $field1 ){
   printf("Expect false value for insert statement, [%s] [%s]\n",odbc_error(),odbc_errormsg());
}else{
   var_dump($field1);
}

$field2 = cubrid_fetch_field("nothisresult");
if(FALSE == $field2 ){
   printf("Expect false value for \"nothisresult\", [%s] [%s]\n",odbc_error(),odbc_errormsg());
}else{
   var_dump($field2);
}
odbc_free_result($result1);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####
bool(true)
object(stdClass)#6 (10) {
  ["name"]=>
  string(2) "c1"
  ["table"]=>
  string(0) ""
  ["def"]=>
  string(0) ""
  ["max_length"]=>
  int(1073741823)
  ["not_null"]=>
  int(0)
  ["primary_key"]=>
  int(0)
  ["unique_key"]=>
  int(0)
  ["multiple_key"]=>
  int(0)
  ["numeric"]=>
  int(0)
  ["blob"]=>
  int(0)
}
#####index < 0#####
Expect false value, [] []
#####index > range#####
Expect false value, [] []
#####insert result#####
Expect false value for insert statement, [] []
Expect false value for "nothisresult", [] []
Finished!