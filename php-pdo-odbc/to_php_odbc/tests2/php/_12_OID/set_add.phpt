--TEST--
cubrid_set_add cubrid_set_drop
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
odbc_exec($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (3, {9,10,11}, {77,999,888,0000}, 'c')");

function fetch_col_b($conn)
{
	$r = odbc_exec($conn, 'SELECT b FROM php_cubrid_test WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return cubrid_odbc_normalize_list_column($v);
}

function fetch_col_c($conn)
{
	$r = odbc_exec($conn, 'SELECT c FROM php_cubrid_test WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return cubrid_odbc_normalize_list_column($v);
}

printf("#####correct add#####\n");
$attr = fetch_col_b($conn);
var_dump($attr);

if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET b = {1, 2, 3, 4} WHERE a = 1')) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET b = {1, 2, 3, 4} WHERE a = 1')) {
	printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET b = {1, 2, 3, 4} WHERE a = 1')) {
	printf("[006] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$attr = fetch_col_b($conn);
var_dump($attr);

$attr = fetch_col_c($conn);
var_dump($attr);

if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET c = {11, 22, 33, 333, 123345566} WHERE a = 1')) {
	printf("[007] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
$attr = fetch_col_c($conn);
var_dump($attr);

printf("#####error add#####\n");
if (!odbc_exec($conn, "UPDATE php_cubrid_test SET b = {1, 2, 3, 'no a int type'} WHERE a = 1")) {
	printf("[008] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col_b($conn);
	var_dump($attr);
}

if (!odbc_exec($conn, 'UPDATE php_cubrid_test SET a = {1} WHERE a = 1')) {
	printf("[009] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	$attr = fetch_col_b($conn);
	var_dump($attr);
}

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct add#####
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
array(4) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
}
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
array(5) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
  [3]=>
  string(3) "333"
  [4]=>
  string(9) "123345566"
}
#####error add#####

Warning: Error: DBMS, -181, Cannot coerce value of domain "character" to domain "integer".%s in %s on line %d
[008] [-181] Cannot coerce value of domain "character" to domain "integer".%s.

Warning: Error: CAS, -10020, The attribute domain must be the set type in %s on line %d
[009] [-10020] The attribute domain must be the set type
Finished!

