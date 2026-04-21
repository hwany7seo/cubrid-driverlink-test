--TEST--
column
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
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

//Data type is character strings
$delete_result2=odbc_exec($conn, "drop class if exists character_tb");
if (!$delete_result2) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result2=odbc_exec($conn, "create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result2) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_exec($conn,"insert into character_tb values('char1','varchar1',N'aaa',N'bbb')");
$result = odbc_exec($conn, "SELECT * FROM character_tb;");
var_dump(odbc_fetch_row($result) );

printf("#####Data type is character strings#####\n");
true;
$field = cubrid_fetch_field($result);

printf("\n---char Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",odbc_field_len($result, 0 + 1));


true;
$field = cubrid_fetch_field($result);
printf("\n---varchar Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",odbc_field_len($result, 1 + 1));


true;
$field = cubrid_fetch_field($result);
printf("\n---nchar Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",odbc_field_len($result, 2 + 1));

true;
$field = cubrid_fetch_field($result);
printf("\n---nchar varying Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",odbc_field_len($result, 3 + 1));


odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
bool(true)
#####Data type is character strings#####

---char Field Properties ---
name: char_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 33
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 34
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 35
zerofill: 0
cubrid_field_len: 5

---varchar Field Properties ---
name: char_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 52
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 53
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 54
zerofill: 0
cubrid_field_len: 11

---nchar Field Properties ---
name: char_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 71
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 72
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 73
zerofill: 0
cubrid_field_len: 20

---nchar varying Field Properties ---
name: char_t
table: 
default value: ""
max lenght: 5
not null: 0
primary_key: 0
unique key: 0
multiple key: 0
numeric: 0
blob: 0

Warning: Undefined property: stdClass::$type in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 89
type: 

Warning: Undefined property: stdClass::$unsigned in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 90
unsigned: 0

Warning: Undefined property: stdClass::$zerofill in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_10_fetch_field/fetch_field_character.php on line 91
zerofill: 0
cubrid_field_len: 1073741823
Finished!