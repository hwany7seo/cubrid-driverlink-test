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
odbc_exec($conn, 'DROP TABLE IF EXISTS unbuffered_tb');
odbc_exec($conn,"CREATE TABLE unbuffered_tb(id int, name varchar(10))");
odbc_exec($conn,"insert into unbuffered_tb values(1,'name1')");
$res=cubrid_unbuffered_query("SELECT * FROM unbuffered_tb ; ", $conn);
if (!$res) {
    printf("[006] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    var_dump(odbc_fetch_array($res));
}
odbc_free_result($res);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(5) "name1"
}
Finished!
