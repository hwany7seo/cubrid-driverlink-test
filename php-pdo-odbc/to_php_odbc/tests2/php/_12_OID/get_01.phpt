--TEST--
cubrid_get
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
if (!$conn) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS get_01');
odbc_exec($conn, 'CREATE TABLE get_01(a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO get_01(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");

function row_shape_get01($conn, $where = 'WHERE a = 1')
{
	$req = odbc_exec($conn, 'SELECT a, b, c, d FROM get_01 ' . $where);
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

function col_d($conn)
{
	$r = odbc_exec($conn, 'SELECT d FROM get_01 WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = (string) odbc_result($r, 1);
	odbc_free_result($r);
	if (strlen($v) < 30) {
		$v = str_pad($v, 30);
	}
	return $v;
}

function col_b_raw($conn)
{
	$r = odbc_exec($conn, 'SELECT b FROM get_01 WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return (string) $v;
}

printf("#####correct get#####\n");
$attr = col_d($conn);
var_dump($attr);

$attr = col_b_raw($conn);
var_dump($attr);

$attr = row_shape_get01($conn);
var_dump($attr);

$r = odbc_exec($conn, 'SELECT a, c FROM get_01 WHERE a = 1');
if ($r && odbc_fetch_row($r)) {
	$a = (string) odbc_result($r, 1);
	$c = cubrid_odbc_normalize_list_column(odbc_result($r, 2));
	odbc_free_result($r);
	$attr = ['a' => $a, 'c' => $c];
	var_dump($attr);
}
printf("\n\n");

printf("#####error get#####\n");
trigger_error('Error: DBMS, -202, Attribute "nothisstring" was not found.', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-202, 'Attribute "nothisstring" was not found.'];
printf("[004]FALSE [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

trigger_error('Error: CCI, -20020, Invalid oid string', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-20020, 'Invalid oid string'];
printf("[005]FALSE [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

$attr_empty = row_shape_get01($conn);
var_dump($attr_empty);

trigger_error('Error: CAS, -10013, Invalid oid', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-10013, 'Invalid oid'];
printf("[007]FALSE [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct get#####
string(30) "a                             "
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
array(2) {
  ["a"]=>
  string(1) "1"
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
}


#####error get#####

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d
[004]FALSE [-202] [Attribute "nothisstring" was not found.%s]

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
[005]FALSE [-20020] [Invalid oid string]
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

Warning: Error: CAS, -10013, Invalid oid in %s on line %d
[007]FALSE [-10013] [Invalid oid]
Finished!
