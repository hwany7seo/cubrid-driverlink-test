--TEST--
cubrid_put
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
define('GREETING', 'Hello world!');
$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!$conn) {
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS put_tb2');
odbc_exec($conn, 'CREATE TABLE put_tb2(a int AUTO_INCREMENT, b set(int),c varchar(30)) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO put_tb2(a, b, c) VALUES (1, {1,2,3},'a')");

function col_b($conn)
{
	$r = odbc_exec($conn, 'SELECT b FROM put_tb2 WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return cubrid_odbc_normalize_list_column($v);
}

function col_c_raw($conn)
{
	$r = odbc_exec($conn, 'SELECT c FROM put_tb2 WHERE a = 1');
	if (!$r || !odbc_fetch_row($r)) {
		return null;
	}
	$v = odbc_result($r, 1);
	odbc_free_result($r);
	return $v;
}

$attr = col_b($conn);
var_dump($attr);

$put4 = odbc_exec($conn, 'UPDATE put_tb2 SET b = {} WHERE a = 1');
if (false == $put4) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("[004] \n");
	$attr = col_b($conn);
	var_dump($attr);
}

$put5 = odbc_exec($conn, 'UPDATE put_tb2 SET c = NULL WHERE a = 1');
if (false == $put5) {
	printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("[005] \n");
	$attr = col_c_raw($conn);
	var_dump($attr === null ? false : $attr);
}

$put6 = odbc_exec($conn, "UPDATE put_tb2 SET c = '" . str_replace("'", "''", GREETING) . "' WHERE a = 1");
if (false == $put6) {
	printf("[006] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("[006] \n");
	$attr = col_c_raw($conn);
	var_dump($attr);
}

$put7 = odbc_exec($conn, "UPDATE put_tb2 SET b = {7, 8, 9}, c = '" . str_replace("'", "''", GREETING) . "' WHERE a = 1");
if (false == $put7) {
	printf("[007] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("[007] \n");
	$r = odbc_exec($conn, 'SELECT b, c FROM put_tb2 WHERE a = 1');
	if ($r && odbc_fetch_row($r)) {
		$rawb = odbc_result($r, 1);
		$rawc = odbc_result($r, 2);
		odbc_free_result($r);
		var_dump((string) $rawb);
		var_dump((string) $rawc);
	}
}

$put8 = odbc_exec($conn, "UPDATE put_tb2 SET a = 8, b = {}, c = '' WHERE a = 1");
if (false == $put8) {
	printf("[008] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("[008] \n");
	$r = odbc_exec($conn, 'SELECT a, c FROM put_tb2 WHERE a = 8');
	if ($r && odbc_fetch_row($r)) {
		$va = odbc_result($r, 1);
		$vc = odbc_result($r, 2);
		odbc_free_result($r);
		var_dump((string) $va);
		var_dump((string) $vc);
	}
}

odbc_close($conn);

print "Finished!\n";
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
[004] 
array(0) {
}
[005] 
bool(false)
[006] 
string(12) "Hello world!"
[007] 
string(9) "{7, 8, 9}"
string(12) "Hello world!"
[008] 
string(1) "8"
string(0) ""
Finished!
