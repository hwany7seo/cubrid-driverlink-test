--TEST--
cubrid_put
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');
require_once dirname(__DIR__, 2) . '/cubrid_odbc_collection.inc';

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!$conn) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS put_tb1');
odbc_exec($conn, "CREATE TABLE put_tb1(a int AUTO_INCREMENT, b set(int), c list(int), d char(30), e blob, f clob) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO put_tb1(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");

function col_b($conn)
{
	$r = odbc_exec($conn, 'SELECT b FROM put_tb1 WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return cubrid_odbc_normalize_list_column($v);
}

$attr = col_b($conn);
var_dump($attr);

if (!odbc_exec($conn, 'UPDATE put_tb1 SET b = {2, 4, 8} WHERE a = 1')) {
	printf("[upd1] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$attr = col_b($conn);
var_dump($attr);

if (!odbc_exec($conn, "UPDATE put_tb1 SET a = 2, b = {7, 8, 9}, c = {77, 88, 99, 999}, d = 'z' WHERE a = 1")) {
	printf("[upd2] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$attr = col_b($conn);
var_dump($attr);

odbc_close($conn);

print 'done!';
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
