--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

//Various types of tables

printf("#####First: Data type is numeric#####\n");
$delete_result1=odbc_exec($conn, "drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result1=odbc_exec($conn, "create class numeric_tb(smallint_t smallint,short_t short, int_t int ,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_exec($conn,"insert into numeric_tb values(-32768,32767,2147483647,-9223372036854775808,0.12345678,12345.6789,-3.402823466E+38,+3.402823466E+38,-3.402823466E+38,-1.7976931348623157E+308);");

$result = odbc_exec($conn, "SELECT smallint_t,short_t,int_t,bigint_t,decimal_t,numeric_t,float_t,real_t, monetary_t FROM numeric_tb;");
var_dump(odbc_fetch_row($result) );

//smallint
cubrid_field_seek($result, 0);
$field = cubrid_fetch_field($result);
$type="smallint";
$index=0;
get_field_property($field,$type,$index,$result);

//short
cubrid_field_seek($result, 1);
$field = cubrid_fetch_field($result);
$type="short";
$index=1;
get_field_property($field,$type,$index,$result);

//bigint
cubrid_field_seek($result, 3);
$field = cubrid_fetch_field($result);
$type="bigint";
$index=3;
get_field_property($field,$type,$index,$result);

//decimal
cubrid_field_seek($result, 4);
$field = cubrid_fetch_field($result);
$type="decimal";
$index=4;
get_field_property($field,$type,$index,$result);

//numeric
cubrid_field_seek($result, 5);
$field = cubrid_fetch_field($result);
$type="numeric";
$index=5;
get_field_property($field,$type,$index,$result);

//float
cubrid_field_seek($result, 6);
$field = cubrid_fetch_field($result);
$type="float";
$index=6;
get_field_property($field,$type,$index,$result);

//real
cubrid_field_seek($result, 7);
$field = cubrid_fetch_field($result);
$type="real";
$index=7;
get_field_property($field,$type,$index,$result);

//monetary
cubrid_field_seek($result, 8);
$field = cubrid_fetch_field($result);
$type="monetary";
$index=8;
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
   printf("cubrid_field_len: %s\n",cubrid_field_len($result,$index));
}

odbc_free_result($result); 
odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####First: Data type is numeric#####
bool(true)


---smallint Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 5


---short Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 5


---bigint Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 19


---decimal Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 15


---numeric Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 38


---float Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 7


---real Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 7


---monetary Field Properties ---
name: smallint_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 1
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_numeric.php on line 91
zerofill: 0
cubrid_field_len: 15
Finished!