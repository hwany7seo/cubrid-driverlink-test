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
$sql = "drop class if exists enum012";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create class enum012(i INT,working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') not null,answers ENUM('Yes', 'No', 'Cancel'))";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);

$days = array("Monday", "Tuesday", "Wednesday");
$answers = array("Yes", "No", "Cancel");
$sql = "insert into enum012(working_days, answers) values(?,?)";
for($i=0; $i<3; $i++){
   $req = odbc_prepare($conn, $sql, CUBRID_INCLUDE_OID);
   cubrid_bind($req, 1, $days[$i] );
   cubrid_bind($req, 2, $answers[$i] );
   odbc_execute($req);
}

//select data
print("*****************************************\n");
$sql = "select * from enum012 ";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while($row = odbc_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-40s", $row[$i]);
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
