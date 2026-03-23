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
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

//drop the class if exist
$sql = "drop table if exists enum032";
$req = odbc_exec($conn, $sql );

//create the class
$sql = "create table enum032(id int, e2 enum('Yes', 'No', 'Cancel'),e3 enum ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), e4 enum('x', 'y', 'z'))";
$req = odbc_exec($conn, $sql );

//insert 
$sql = "insert into enum032 values(1,1,1,1),(6, 1, 4, 'z')";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
// select
$sql = "select * from enum032 order by id";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*************Before Execute*******************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

//prepare
$sql = "prepare x from 'update enum032 set e3=? where id=1'";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

//execute
$sql = "execute x using -1";
//$req = odbc_prepare($conn, $sql );
odbc_exec($conn, $sql);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
// select
$sql = "select * from enum032 order by id";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*******************After Execute using -1****************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

$sql = "execute x using null";
//$req = odbc_prepare($conn, $sql );
odbc_exec($conn, $sql);
// select
$sql = "select * from enum032 order by id";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("****************After Excute using NULL****************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

$sql = "execute x using 'Saturday'";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
// select
$sql = "select * from enum032 order by id";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}


//prepare
$sql = "prepare x from 'update enum032 set e3=? where id=1'";
$req = odbc_prepare($conn, $sql);
odbc_execute($req);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
//execute
$sql = "execute x using ''";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
if (!$req) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

//select data
$sql = "select cast(working_days as int), cast(answers as int) from enum032";
$req = odbc_prepare($conn, $sql );
odbc_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

odbc_close($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECTF--
*************Before Execute*******************
id                            e2                            e3                            e4                            
1                             Yes                           Sunday                        x                             
6                             Yes                           Wednesday                     z                             

Warning: Error: DBMS, -181, Cannot coerce value of domain "integer" to domain "enum".%s in %s on line %d
*******************After Execute using -1****************
id                            e2                            e3                            e4                            
1                             Yes                           Sunday                        x                             
6                             Yes                           Wednesday                     z                             
****************After Excute using NULL****************
id                            e2                            e3                            e4                            
1                             Yes                                                         x                             
6                             Yes                           Wednesday                     z                             
*****************************************
id                            e2                            e3                            e4                            
1                             Yes                           Saturday                      x                             
6                             Yes                           Wednesday                     z                             

Warning: Error: DBMS, -494, Semantic: before '  as int), cast(answers as int) from enum032'
Attribute "working_days" was not found. select  cast(working_days as integer),  cast(answers as inte...%s in %s on line %d

Warning: cubrid_execute() expects parameter 1 to be resource, bool given in %s on line %d

Warning: cubrid_column_names() expects parameter 1 to be resource, bool given in %s on line %d

Warning: cubrid_column_types() expects parameter 1 to be resource, bool given in %s on line %d

Warning: count(): Parameter must be an array or an object that implements Countable in %s on line %d
*****************************************


Warning: odbc_fetch_row() expects parameter 1 to be resource, bool given in %s on line %d
Finished
