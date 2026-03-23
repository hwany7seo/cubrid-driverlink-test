--TEST--
cubrid_seq_drop
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
    exit(1);
}

@odbc_exec($conn, "DROP TABLE seq_drop_tb");
odbc_exec($conn, "CREATE TABLE seq_drop_tb (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO seq_drop_tb(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");

if (!$req = odbc_exec($conn, "select * from seq_drop_tb", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

if (!cubrid_seq_drop($conn, $oid, "c", 4)) {
    printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

odbc_free_result($req);
odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(4) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
  [3]=>
  string(3) "333"
}
array(3) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
}
done!
