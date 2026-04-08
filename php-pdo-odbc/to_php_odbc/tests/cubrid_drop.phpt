--TEST--
cubrid_drop
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

function cubrid_odbc_php_cubrid_test_row_shape($a, $b_raw, $c_raw, $d_raw)
{
	$b = cubrid_odbc_normalize_list_column($b_raw);
	$c = cubrid_odbc_normalize_list_column($c_raw);
	if ($b === null || $c === null) {
		return null;
	}
	$d = (string) $d_raw;
	if (strlen($d) < 30) {
		$d = str_pad($d, 30);
	}
	return [
		'a' => (string) $a,
		'b' => $b,
		'c' => $c,
		'd' => $d,
	];
}

function fetch_row_shape($conn, $whereSql)
{
	$req = odbc_exec($conn, 'SELECT a, b, c, d FROM php_cubrid_test ' . $whereSql);
	if (!$req || !odbc_fetch_row($req)) {
		return null;
	}
	$shape = cubrid_odbc_php_cubrid_test_row_shape(
		odbc_result($req, 1),
		odbc_result($req, 2),
		odbc_result($req, 3),
		odbc_result($req, 4)
	);
	odbc_free_result($req);
	return $shape;
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

$shape = fetch_row_shape($conn, 'WHERE a = 1 ORDER BY a');
if ($shape === null) {
	printf("[005] First row fetch failed\n");
	exit(1);
}
var_dump($shape);

if (!odbc_exec($conn, 'DELETE FROM php_cubrid_test WHERE a = 1')) {
	printf("[006] DELETE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_commit($conn)) {
	printf("[007] commit failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$shape = fetch_row_shape($conn, 'WHERE a = 2 ORDER BY a');
if ($shape === null) {
	printf("[008] Second row fetch failed\n");
	exit(1);
}
var_dump($shape);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
<?php
require_once('clean_table.inc');
?>
--EXPECTF--
array(4) {
  ["a"]=>
  string(1) "1"
  ["b"]=>
  array(3) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
  }
  ["c"]=>
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
  ["d"]=>
  string(30) "a                             "
}
array(4) {
  ["a"]=>
  string(1) "2"
  ["b"]=>
  array(3) {
    [0]=>
    string(1) "4"
    [1]=>
    string(1) "5"
    [2]=>
    string(1) "7"
  }
  ["c"]=>
  array(4) {
    [0]=>
    string(2) "44"
    [1]=>
    string(2) "55"
    [2]=>
    string(2) "66"
    [3]=>
    string(3) "666"
  }
  ["d"]=>
  string(30) "b                             "
}
done!
