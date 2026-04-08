--TEST--
cubrid_set_drop
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

function cubrid_odbc_normalize_list_column($v)
{
	if (is_array($v)) {
		$out = [];
		foreach ($v as $x) {
			$out[] = (string) $x;
		}
		return $out;
	}
	if (!is_string($v)) {
		return null;
	}
	$v = trim($v);
	if (strlen($v) < 2 || $v[0] !== '{') {
		return null;
	}
	$inner = substr($v, 1, -1);
	if ($inner === '') {
		return [];
	}
	$out = [];
	foreach (explode(',', $inner) as $p) {
		$out[] = trim($p);
	}
	return $out;
}

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
cubrid_odbc_set_last_connection($conn);

@odbc_exec($conn, 'DROP TABLE IF EXISTS php_cubrid_test');
if (!odbc_exec($conn, 'CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID')) {
	printf("[002] CREATE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')")) {
	printf("[003] INSERT 1 failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')")) {
	printf("[004] INSERT 2 failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$req = odbc_exec($conn, 'SELECT b FROM php_cubrid_test WHERE a = 1');
if (!$req || !odbc_fetch_row($req)) {
	printf("[005] SELECT failed\n");
	exit(1);
}
$raw = odbc_result($req, 1);
odbc_free_result($req);

$attr = cubrid_odbc_normalize_list_column($raw);
if ($attr === null) {
	printf("[006] Unexpected SET form\n");
	exit(1);
}
var_dump($attr);

if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET b = {2, 3} WHERE a = 1')) {
	printf("[007] UPDATE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_commit($conn)) {
	printf("[008] commit failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$req = odbc_exec($conn, 'SELECT b FROM php_cubrid_test WHERE a = 1');
if (!$req || !odbc_fetch_row($req)) {
	printf("[009] Second SELECT failed\n");
	exit(1);
}
$raw2 = odbc_result($req, 1);
odbc_free_result($req);

$attr = cubrid_odbc_normalize_list_column($raw2);
if ($attr === null) {
	printf("[010] Unexpected SET after UPDATE\n");
	exit(1);
}
var_dump($attr);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
<?php
require_once('clean_table.inc');
?>
--EXPECTF--
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
array(2) {
  [0]=>
  string(1) "2"
  [1]=>
  string(1) "3"
}
done!
