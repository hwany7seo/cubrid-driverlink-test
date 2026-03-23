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
$sql = "drop class if exists t3";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create table t3(e1 enum('a', 'b'), e2 enum('Yes', 'No', 'Cancel'), e3 enum ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday','Saturday'),e4 enum('x', 'y', 'z'))";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);

//insert 
$sql = "insert into t3 values(1, 1, 1, 1), (2, 3, 7, 3), ('b', 'No', 'Tuesday', 'y'), ('a', 'Yes', 'Friday', 'x'),('a', 'Cancel', 'Thursday', 'z'), ('b', 1, 4, 'z')";
$req = odbc_exec($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "update t3 set e1=?, e2=? where e3=?";
$e1_value = "b" ;
$e2_value = "No" ;
$e3_value = "Friday"; ;
$req = odbc_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_bind($req, 1, $e1_value );
cubrid_bind($req, 2, $e2_value );
cubrid_bind($req, 3, $e3_value );

odbc_execute($req);

//select data
print("*****************************************\n");
$sql = "select * from t3 order by 1, 2, 3, 4";
$req = odbc_prepare($conn, $sql, CUBRID_INCLUDE_OID);
odbc_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
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
--EXPECT--
*****************************************
e1                            e2                            e3                            e4                            
a                             Yes                           Sunday                        x                             
a                             Cancel                        Thursday                      z                             
b                             Yes                           Wednesday                     z                             
b                             No                            Tuesday                       y                             
b                             No                            Friday                        x                             
b                             Cancel                        Saturday                      z                             
Finished
