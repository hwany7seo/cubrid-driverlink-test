--TEST--
cubrid_seq_put
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

odbc_exec($conn, "DROP TABLE IF EXISTS php_cubrid_test");
odbc_exec($conn, "CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')");

if (!$req = odbc_exec($conn, "select * from php_cubrid_test", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

if (!cubrid_seq_put($conn, $oid, "c", 1, "111")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

odbc_close_request($req);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
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
array(4) {
  [0]=>
  string(3) "111"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
  [3]=>
  string(3) "333"
}
done!
