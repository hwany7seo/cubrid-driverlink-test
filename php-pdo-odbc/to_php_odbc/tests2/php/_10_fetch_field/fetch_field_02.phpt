--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//table contains all kind of types 
include_once("connect.inc");
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
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
   printf("Expect false value, [%d] [%s]\n",odbc_error(),odbc_errormsg());   
}else{
   var_dump($field);
}
printf("#####index > range#####\n");
$field = cubrid_fetch_field($result,3);
if(FALSE == $field ){
   printf("Expect false value, [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
   var_dump($field);
}
odbc_free_result($result);

//insert result
printf("#####insert result#####\n");
$result1=odbc_exec($conn,"insert into field2_tb(c1,c2,c3) values('insert string','insert char',2)");
$field1 = cubrid_fetch_field($result1);
if(FALSE == $field1 ){
   printf("Expect false value for insert statement, [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
   var_dump($field1);
}

$field2 = cubrid_fetch_field("nothisresult");
if(FALSE == $field2 ){
   printf("Expect false value for \"nothisresult\", [%d] [%s]\n",odbc_error(),odbc_errormsg());
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
array(3) {
  [0]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
}
object(stdClass)#1 (13) {
  ["name"]=>
  string(2) "c1"
  ["table"]=>
  string(13) "dba.field2_tb"
  ["def"]=>
  string(4) "NULL"
  ["max_length"]=>
  int(0)
  ["not_null"]=>
  int(1)
  ["primary_key"]=>
  int(1)
  ["unique_key"]=>
  int(1)
  ["multiple_key"]=>
  int(0)
  ["numeric"]=>
  int(0)
  ["blob"]=>
  int(0)
  ["type"]=>
  string(7) "varchar"
  ["unsigned"]=>
  int(0)
  ["zerofill"]=>
  int(0)
}
#####index < 0#####

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
Expect false value, [-20013] [Column index is out of range]
#####index > range#####

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
Expect false value, [-20013] [Column index is out of range]
#####insert result#####
Expect false value for insert statement, [0] []

Warning: cubrid_fetch_field() expects parameter 1 to be resource, string given in %s on line %d
Expect false value for "nothisresult", [0] []
Finished!
