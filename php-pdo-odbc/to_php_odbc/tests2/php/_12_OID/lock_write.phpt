--TEST--
cubrid_lock_write
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
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS lock_write');
odbc_exec($conn, 'CREATE TABLE lock_write (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO lock_write(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
odbc_exec($conn, "INSERT INTO lock_write(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')");

$r = odbc_exec($conn, 'SELECT b FROM lock_write WHERE a = 1');
if (!$r || !odbc_fetch_row($r)) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$rawb = odbc_result($r, 1);
	odbc_free_result($r);
	var_dump((string) $rawb);
}

if (!odbc_exec($conn, 'UPDATE lock_write SET b = {2, 4, 8} WHERE a = 1')) {
	printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$r2 = odbc_exec($conn, 'SELECT b FROM lock_write WHERE a = 1');
if (!$r2 || !odbc_fetch_row($r2)) {
	printf("[004] select failed\n");
} else {
	$raw2 = odbc_result($r2, 1);
	odbc_free_result($r2);
	$attr = cubrid_odbc_normalize_list_column($raw2);
	var_dump($attr);
}

odbc_close($conn);

print 'done!';
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
