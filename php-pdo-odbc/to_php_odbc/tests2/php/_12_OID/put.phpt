--TEST--
cubrid_put
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
odbc_exec($conn, "drop table if exists put_tb1");
odbc_exec($conn, "create table put_tb1(a int AUTO_INCREMENT, b set(int), c list(int), d char(30), e blob, f clob) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO put_tb1(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");

if (!$req = odbc_exec($conn, "select * from put_tb1", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

cubrid_put($conn, $oid, "b", array(2, 4, 8));

$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

$attr = cubrid_put($conn, $oid, array("a" => 2, "b" => array(7,8,9), "c" => array(77,88,99,999), "d" => "z"));

$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
array(3) {
  [0]=>
  string(1) "2"
  [1]=>
  string(1) "4"
  [2]=>
  string(1) "8"
}
array(3) {
  [0]=>
  string(1) "7"
  [1]=>
  string(1) "8"
  [2]=>
  string(1) "9"
}
done!
