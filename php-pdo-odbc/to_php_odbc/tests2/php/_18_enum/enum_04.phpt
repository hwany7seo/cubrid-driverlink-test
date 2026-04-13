--TEST--
enum type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

//drop the class if exist
$sql = "drop table if exists enum012";
$req = odbc_exec($conn, $sql);

//create the class
$sql = "create table enum012(e1 enum('a', 'b'), e2 enum('Yes', 'No', 'Cancel'))";
$req = odbc_exec($conn, $sql);

//insert values into the class
$sql = "insert into enum012 values (1, 1), (1, 2), (1, 3), (2, 1), (2, 2), (2, 3)";
$req = odbc_exec($conn, $sql);
//select data
print("*****************************************\n");
$sql = "select * from enum012 order by 1, 2";
$req = odbc_exec($conn, $sql);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-40s", $__c !== false ? $__c : '');
}
print("\n");
}

//update data
$sql = "update enum012 set e1=cast(e2 as int) where e2 < 3";
$req = odbc_exec($conn, $sql);
//select data
print("*****************************************\n");
$sql = "select * from enum012 order by 1, 2";
$req = odbc_exec($conn, $sql);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-40s", $__c !== false ? $__c : '');
}
print("\n");
}

//update data
$sql = "update enum012 set e2=e1 + 1";
$req = odbc_exec($conn, $sql);
//select data
print("*****************************************\n");
$sql = "select * from enum012 order by 1, 2";
$req = odbc_exec($conn, $sql);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-40s", $__c !== false ? $__c : '');
}
print("\n");
}

//update data
$sql = "update enum012 set e1='b', e2='No'";
$req = odbc_exec($conn, $sql);

//select data
print("*****************************************\n");
$sql = "select * from enum012 order by 1, 2";
$req = odbc_exec($conn, $sql);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-40s", $__c !== false ? $__c : '');
}
print("\n");
}

odbc_close($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
*****************************************
e1                                      e2                                      
a                                       Yes                                     
a                                       No                                      
a                                       Cancel                                  
b                                       Yes                                     
b                                       No                                      
b                                       Cancel                                  
*****************************************
e1                                      e2                                      
a                                       Yes                                     
a                                       Yes                                     
a                                       Cancel                                  
b                                       No                                      
b                                       No                                      
b                                       Cancel                                  
*****************************************
e1                                      e2                                      
a                                       No                                      
a                                       No                                      
a                                       No                                      
b                                       Cancel                                  
b                                       Cancel                                  
b                                       Cancel                                  
*****************************************
e1                                      e2                                      
b                                       No                                      
b                                       No                                      
b                                       No                                      
b                                       No                                      
b                                       No                                      
b                                       No                                      
Finished
