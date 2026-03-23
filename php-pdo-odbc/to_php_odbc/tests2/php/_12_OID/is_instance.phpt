--TEST--
cubrid_is_instance
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
odbc_exec($conn,"drop table if exists code;");
odbc_exec($conn,"create table code(last_name varchar(10), first_name varchar(20)) DONT_REUSE_OID");
odbc_exec($conn,"insert into code values('X','Mixed'),('W','Woman'),('M','Man'),('B','Bronze')");
if (!($req = odbc_exec($conn, 'SELECT * FROM code', CUBRID_INCLUDE_OID))) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$res = cubrid_is_instance($conn, $oid);
if ($res == 1) {
    printf("Intance pointed by %s exists.\n", $oid);
} else {
    printf ("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Intance pointed by %s exists.
done!
