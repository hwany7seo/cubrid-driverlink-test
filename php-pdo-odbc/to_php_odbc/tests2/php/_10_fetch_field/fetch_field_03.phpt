--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//table has primary key or foreign key
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn,"drop table if exists fetch_tb;");
odbc_exec($conn,"CREATE TABLE fetch_tb(c1 string primary key, c2 char(20) not null, c3 int default -2147483648 unique key, c4 double default 22.22, c5 time default TIME '23:59:59', c6 date, c7 TIMESTAMP default TIMESTAMP  '2038-01-19 12:14:07',c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
odbc_exec($conn,"insert into fetch_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '1977-08-14', TIMESTAMP '1977-08-14 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");

print("#####positive example#####\n");
$result=odbc_exec($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10) as c10, BLOB_TO_BIT(c11) from fetch_tb");
var_dump(odbc_fetch_row($result) );

//string
true;
$field = cubrid_fetch_field($result);
$type="string";
$index=0;
get_field_property($field,$type,$index,$result);

//char
$type="char";
$index=1;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//int
$type="int";
$index=2;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//double
$type="double";
$index=3;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//time
$type="time";
$index=4;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//date
$type="date";
$index=5;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//timestamp
$type="timestamp";
$index=6;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//bit
$type="bit";
$index=7;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//numeric
$type="numeric";
$index=8;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//clob
$type="clob";
$index=9;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//blob
$type="blob";
$index=10;
true;
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

function get_field_property($field,$type,$index,$result){
   printf("\n\n---$type Field Properties ---\n");
   printf("%s %s\n", "name:", $field->name);
   printf("%s %s\n", "table:", $field->table);
   printf("%s \"%s\"\n", "default value:", $field->def);
   printf("%s %d\n", "max lenght:", $field->max_length);
   printf("%s %d\n", "not null:", $field->not_null);
   printf("%s %d\n", "primary_key:", $field->primary_key);
   printf("%s %d\n", "unique key:", $field->unique_key);
   printf("%s %d\n", "multiple key:", $field->multiple_key);
   printf("%s %d\n", "numeric:", $field->numeric);
   printf("%s %d\n", "blob:", $field->blob);
   printf("%s %s\n", "type:", $field->type);
   printf("%s %d\n", "unsigned:", $field->unsigned);
   printf("%s %d\n", "zerofill:", $field->zerofill);
   printf("cubrid_field_len: %s\n",odbc_field_len($result, $index + 1));
}

odbc_free_result($result);
odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
bool(true)


---string Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 1073741823


---char Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 20


---int Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 10


---double Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 15


---time Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 11


---date Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 10


---timestamp Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 23


---bit Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 1


---numeric Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 13


---clob Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 1073741823


---blob Field Properties ---
name: c1
table: 
default value: ""
max lenght: 1073741823
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 102
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 103
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_03.php on line 104
zerofill: 0
cubrid_field_len: 134217728
Finished!