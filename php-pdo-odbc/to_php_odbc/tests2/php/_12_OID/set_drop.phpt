--TEST--
cubrid_set_add cubrid_set_drop
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only OID API — Not supported by PHP ODBC");
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
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (3, {9,10,11}, {77,999,888,0000}, 'c')");

function fetch_col($conn, $col)
{
	$r = odbc_exec($conn, "SELECT $col FROM php_cubrid_test WHERE a = 1");
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return cubrid_odbc_normalize_list_column($v);
}

function set_remove_sql($conn, $col, $elem, $asInt = true)
{
	$raw = null;
	$r = odbc_exec($conn, "SELECT $col FROM php_cubrid_test WHERE a = 1");
	if ($r && odbc_fetch_row($r)) {
		$raw = odbc_result($r, 1);
	}
	if ($r) {
		odbc_free_result($r);
	}
	$arr = cubrid_odbc_normalize_list_column($raw);
	if ($arr === null) {
		return false;
	}
	$out = [];
	foreach ($arr as $x) {
		if ((string) $x !== (string) $elem) {
			$out[] = $x;
		}
	}
	if ($asInt) {
		$lit = '{' . implode(',', array_map(static fn ($x) => (string) (int) $x, $out)) . '}';
	} else {
		$lit = '{' . implode(',', $out) . '}';
	}
	return odbc_exec($conn, "UPDATE php_cubrid_test SET $col = $lit WHERE a = 1");
}

printf("#####error drop#####\n");
if (!set_remove_sql($conn, 'b', '4', true)) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col($conn, 'b');
	var_dump($attr);
}

if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET a = {1} WHERE a = 1')) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col($conn, 'a');
	var_dump($attr);
}

if (!set_remove_sql($conn, 'c', '1111', true)) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col($conn, 'c');
	var_dump($attr);
}

if (!odbc_exec($conn, "UPDATE php_cubrid_test SET b = {1, 2, 'no this value'} WHERE a = 1")) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col($conn, 'b');
	var_dump($attr);
}

printf("#####correct drop#####\n");
foreach ([1, 2, 3, 4] as $v) {
	set_remove_sql($conn, 'b', (string) $v, true);
}
$attr = fetch_col($conn, 'b');
var_dump($attr);

foreach ([11, 22, 33, 333] as $v) {
	set_remove_sql($conn, 'c', (string) $v, true);
}
$attr = fetch_col($conn, 'c');
var_dump($attr);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####error drop#####
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}

Warning: Error: CAS, -10020, The attribute domain must be the set type in %s on line %d
[004] [-10020] The attribute domain must be the set type
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

Warning: Error: DBMS, -181, Cannot coerce value of domain "character" to domain "integer".%s in %s on line %d
[004] [-181] Cannot coerce value of domain "character" to domain "integer".%s.
#####correct drop#####
array(0) {
}
array(0) {
}
Finished!

