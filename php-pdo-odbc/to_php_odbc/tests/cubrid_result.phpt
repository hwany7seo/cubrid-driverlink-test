--TEST--
cubrid_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
$req = odbc_exec($conn, 'SELECT s_name, f_name FROM code');
if (!$req) {
	printf("[002] [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$byName = [];
while (odbc_fetch_row($req)) {
	$sn = (string) odbc_result($req, 1);
	$byName[$sn] = [(string) odbc_result($req, 1), (string) odbc_result($req, 2)];
}
odbc_free_result($req);

$order = ['X', 'W', 'M', 'B', 'S', 'G'];
$rows = [];
foreach ($order as $name) {
	if (isset($byName[$name])) {
		$rows[] = $byName[$name];
	}
}

var_dump($rows[0][0]);
var_dump($rows[0][1]);
var_dump($rows[5][1]);
var_dump($rows[4][1]);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
string(1) "X"
string(5) "Mixed"
string(4) "Gold"
string(6) "Silver"
done!
