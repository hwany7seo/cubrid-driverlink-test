--TEST--
cubrid_lock_read
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
odbc_exec($conn, 'DROP TABLE IF EXISTS php_cubrid_test');
odbc_exec($conn, 'CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')");

$r = odbc_exec($conn, 'SELECT b FROM php_cubrid_test WHERE a = 1');
if (!$r || !odbc_fetch_row($r)) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$rawb = odbc_result($r, 1);
	odbc_free_result($r);
	var_dump((string) $rawb);
}

function row_shape($conn, $whereSql)
{
	$req = odbc_exec($conn, 'SELECT a, b, c, d FROM php_cubrid_test ' . $whereSql);
	if (!$req || !odbc_fetch_row($req)) {
		return null;
	}
	$a = odbc_result($req, 1);
	$b = cubrid_odbc_normalize_list_column(odbc_result($req, 2));
	$c = cubrid_odbc_normalize_list_column(odbc_result($req, 3));
	$d = (string) odbc_result($req, 4);
	odbc_free_result($req);
	if ($b === null || $c === null) {
		return null;
	}
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

$attr = row_shape($conn, 'WHERE a = 1 ORDER BY a');
var_dump($attr);

odbc_close($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
string(9) "{1, 2, 3}"
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
done!
