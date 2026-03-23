--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

odbc_exec($conn,"drop table if EXISTS index_tb;");
odbc_exec($conn,"CREATE TABLE index_tb(id INT PRIMARY KEY,phone VARCHAR(10),address string);");
odbc_exec($conn,"create index index_tb_index on index_tb(address)");

$result=odbc_exec($conn,"select * from  index_tb;");
$col_num = odbc_num_fields($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i)); 
}
odbc_free_result($result);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
id                             not_null primary_key unique_key
phone                          
address                        
Finished!
