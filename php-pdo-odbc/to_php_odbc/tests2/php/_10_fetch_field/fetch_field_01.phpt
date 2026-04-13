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
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn,"drop table if exists field1_tb;");
odbc_exec($conn,"CREATE TABLE field1_tb(c1 string primary key, c2 char(20) not null, c3 int default -2147483648 unique key);");
odbc_exec($conn,"insert into field1_tb values('string111111','char11111',1)");

print("#####negative example#####\n");
$result=odbc_exec($conn,"select * from field1_tb where c3 > 10");
var_dump(odbc_fetch_row($result) );

//fetch char first
cubrid_field_seek($result, 1);
$field = cubrid_fetch_field($result);
var_dump($field);

//index<0
cubrid_field_seek($result, -1);
$field1 = cubrid_fetch_field($result);
printf("\n\n---index < 0 Field Properties ---\n");
printf("%s %s\n", "name:", $field1->name);
printf("%s \"%s\"\n", "default value:", $field1->def);


//index > range 
cubrid_field_seek($result, 3);
$field2 = cubrid_fetch_field($result);
printf("\n\n---index > range Field Properties ---\n");
printf("%s %s\n", "name:", $field2->name);
printf("%s %s\n", "table:", $field2->table);


odbc_free_result($result);
odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####
bool(false)
object(stdClass)#1 (13) {
  ["name"]=>
  string(2) "c2"
  ["table"]=>
  string(13) "dba.field1_tb"
  ["def"]=>
  string(4) "NULL"
  ["max_length"]=>
  int(0)
  ["not_null"]=>
  int(1)
  ["primary_key"]=>
  int(0)
  ["unique_key"]=>
  int(0)
  ["multiple_key"]=>
  int(1)
  ["numeric"]=>
  int(0)
  ["blob"]=>
  int(0)
  ["type"]=>
  string(4) "char"
  ["unsigned"]=>
  int(0)
  ["zerofill"]=>
  int(0)
}

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d


---index < 0 Field Properties ---
name: c3
default value: "-2147483648"

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d


---index > range Field Properties ---

Notice: Trying to get property 'name' of non-object in %s on line %d
name: 

Notice: Trying to get property 'table' of non-object in %s on line %d
table: 
Finished!
