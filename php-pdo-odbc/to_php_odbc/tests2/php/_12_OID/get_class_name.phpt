--TEST--
cubrid_get_class_name
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS class_name_tb');
$sql ="CREATE TABLE class_name_tb(id int, name varchar(10)) DONT_REUSE_OID";
odbc_exec($conn,$sql);
odbc_exec($conn,"insert into class_name_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

if (!$req = odbc_exec($conn, "select * from class_name_tb", CUBRID_INCLUDE_OID)) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

$table_name = cubrid_get_class_name($conn, $oid);

print_r($table_name);

print "\n";
print "done!"
?>
--CLEAN--
--EXPECTF--
dba.class_name_tb
done!
