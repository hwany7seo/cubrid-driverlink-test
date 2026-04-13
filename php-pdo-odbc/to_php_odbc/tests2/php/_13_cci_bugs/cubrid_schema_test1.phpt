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

$schema4 = cubrid_schema($conn, CUBRID_SCH_CLASS, "db_partition",'nothis attr_name');
if ($schema4 == false) {
    printf("[004] Expecting false, got [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
    printf("schema value4: \n");
    var_dump($schema4);
}
odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
schema value4: 
array(1) {
  [0]=>
  array(3) {
    ["NAME"]=>
    string(12) "db_partition"
    ["TYPE"]=>
    string(1) "0"
    ["REMARKS"]=>
    NULL
  }
}
Finished!
