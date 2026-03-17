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

$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");

if (!$req = odbc_exec($conn, "select * from code", CUBRID_INCLUDE_OID)) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

$table_name = cubrid_get_class_name($conn, $oid);

print_r($table_name);

print "\n";
print "done!"
?>
--CLEAN--
--EXPECTF--
public.code
done!
