--TEST--
cubrid_lock_write
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
odbc_exec($conn, "drop table if exists lock_write");
odbc_exec($conn, "CREATE TABLE lock_write (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO lock_write(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
odbc_exec($conn, "INSERT INTO lock_write(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')");

if (!$req = odbc_exec($conn, "select * from lock_write", CUBRID_INCLUDE_OID)) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

if (!cubrid_lock_write($conn, $oid)) {
    printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$attr = cubrid_get($conn, $oid, "b");
var_dump($attr);

cubrid_put($conn, $oid, "b", array(2,4,8));

$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

odbc_free_result($req);
odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
string(9) "{1, 2, 3}"
array(3) {
  [0]=>
  string(1) "2"
  [1]=>
  string(1) "4"
  [2]=>
  string(1) "8"
}
done!
