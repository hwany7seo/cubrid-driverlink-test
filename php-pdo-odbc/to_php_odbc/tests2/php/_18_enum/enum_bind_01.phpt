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
$sql = "drop class if exists enum012";
$req = odbc_exec($conn, $sql);

//create the class
$sql = "create class enum012(i INT,working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') not null,answers ENUM('Yes', 'No', 'Cancel'))";
$req = odbc_exec($conn, $sql);

$days = array("Monday", "Tuesday", "Wednesday");
$answers = array("Yes", "No", "Cancel");
$sql = "insert into enum012(working_days, answers) values(?,?)";
for($i=0; $i<3; $i++){
   $req = odbc_prepare($conn, $sql);
   cubrid_bind($req, 1, $days[$i] );
   cubrid_bind($req, 2, $answers[$i] );
   cubrid_execute($req);
}

//select data
print("*****************************************\n");
$sql = "select * from enum012 ";
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
i                                       working_days                            answers                                 
                                        Monday                                  Yes                                     
                                        Tuesday                                 No                                      
                                        Wednesday                               Cancel                                  
Finished
