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
cubrid_set_autocommit($conn, false);

$sql = "drop class if exists enum01";
$req = odbc_exec($conn, $sql);
odbc_commit($conn);

$sql = "create class enum01(i INT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), answers ENUM('Yes', 'No', 'Cancel'))";
$req = odbc_exec($conn, $sql);
odbc_commit($conn);

$sql = "insert into enum01 values(3, 'Wednesday','Cancel'),(2,'Tuesday','No'),(1,1,1)";
$req = odbc_exec($conn, $sql);

$sql = "select * from enum01";
$req_holdability = odbc_prepare($conn, $sql);
cubrid_execute($req_holdability);
$column_names1 = cubrid_column_names($req_holdability);
$column_types1 = cubrid_column_types($req_holdability);
$size = count($column_names1);
//fetch all
print("\n**********************Fetch all************************\n");
$row_size = 0;
while (odbc_fetch_row($req_holdability)) {
    $row_size++;
for ($i = 0; $i < $size; $i++) {
   $__c = cubrid_odbc_result_cell($req_holdability, $i);
   printf("%-30s", $__c !== false ? $__c : '');
}
print("\n");
}
print("row_size: ".$row_size."\n");

odbc_close($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
**********************Fetch all************************
3                             Wednesday                     Cancel                        
2                             Tuesday                       No                            
1                             Monday                        Yes                           
row_size: 3
Finished
