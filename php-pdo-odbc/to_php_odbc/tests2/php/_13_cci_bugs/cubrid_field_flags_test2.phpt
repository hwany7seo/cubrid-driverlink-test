--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, 'DROP TABLE IF EXISTS blob_tb');
odbc_exec($conn,"CREATE TABLE blob_tb(id int, c10 clob,c11 blob);");
odbc_exec($conn,"insert into blob_tb values( 1, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
$result=odbc_exec($conn,"select id as int_t, c10, BLOB_TO_BIT(c11) from blob_tb");
$col_num = odbc_num_fields($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", odbc_field_name($result, $i + 1), cubrid_field_flags($result, $i)); 
}

odbc_free_result($result);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
int_t                          
c10                            
blob_to_bit(c11)               
Finished!
