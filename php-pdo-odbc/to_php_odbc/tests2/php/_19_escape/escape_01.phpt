--TEST--
enum type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--XFAIL--
ODBC driver returns garbage bytes for ENUM values.
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$sql = "drop class if exists escape01";
$req = odbc_exec($conn, $sql);

$unescaped_str = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
$escaped_str = cubrid_real_escape_string($unescaped_str);

$len = strlen($unescaped_str);

$sql = "create class escape01(i INT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), answers ENUM('Yes', 'No', 'Cancel'), t char($len))";
$req = odbc_exec($conn, $sql);

$sql = "insert into escape01 values(1,1,1,'$escaped_str'),(2,'Tuesday','No','$escaped_str'), (3, 'Wednesday','Cancel','$escaped_str')";
$req = odbc_exec($conn, $sql);
#$req = odbc_exec($conn, $sql);

$sql = "select * from escape01";
$req = odbc_exec($conn, $sql);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while (odbc_fetch_row($req)) {
for ($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = odbc_field_len($req, $i + 1);
    $__c = odbc_result($req, $i + 1);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $__c !== false ? $__c : '');
}
}

odbc_close($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
column_name                              column_type          column_len           column_value                            
i                                        integer              11                   1                                       
working_days                             enum                 0                    Monday                                  
answers                                  enum                 0                    Yes                                     
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
i                                        integer              11                   2                                       
working_days                             enum                 0                    Tuesday                                 
answers                                  enum                 0                    No                                      
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
i                                        integer              11                   3                                       
working_days                             enum                 0                    Wednesday                               
answers                                  enum                 0                    Cancel                                  
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
Finished
