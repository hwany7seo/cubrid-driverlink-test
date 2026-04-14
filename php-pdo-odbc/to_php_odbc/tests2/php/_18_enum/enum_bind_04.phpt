--TEST--
enum type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--XFAIL--
ODBC driver returns garbage bytes for ENUM values.
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

//drop the class if exist
$sql = "drop class if exists enum02";
$req = odbc_exec($conn, $sql);

//create the class
$sql = "create class enum02(i INT AUTO_INCREMENT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),answers ENUM('Yes', 'No', 'Cancel'))";
$req = odbc_exec($conn, $sql);

//insert 
$days = array("Monday", "Tuesday", "Wednesday");
$answers = array("Yes", "No", "Cancel");
$sql = "insert into enum02(working_days, answers) values(?,?)";
for($i=0; $i<3; $i++){
   $req = odbc_prepare($conn, $sql);
   cubrid_bind($req, 1, $days[$i] );
   cubrid_bind($req, 2, $answers[$i] );
   cubrid_execute($req);
}

// select
$sql = "select * from enum02";
$req = odbc_prepare($conn, $sql);
cubrid_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-30s", $__c !== false ? $__c : '');
}
print("\n");
}

//select data
$sql = "select cast(working_days as int), cast(answers as int) from enum02";
$req = odbc_prepare($conn, $sql);
cubrid_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while (odbc_fetch_row($req)) {
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req, $i);
   printf("%-30s", $__c !== false ? $__c : '');
}
print("\n");
}

odbc_close($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
*****************************************
i                             working_days                  answers                       
1                             Monday                        Yes                           
2                             Tuesday                       No                            
3                             Wednesday                     Cancel                        
*****************************************
cast(working_days as integer) cast(answers as integer)      
1                             1                             
2                             2                             
3                             3                             
Finished
