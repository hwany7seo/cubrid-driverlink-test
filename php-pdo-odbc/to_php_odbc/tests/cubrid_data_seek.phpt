--TEST--
cubrid_data_seek
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
	$byName[$sn] = [$sn, (string) odbc_result($req, 2)];
}
odbc_free_result($req);

$order = ['X', 'W', 'M', 'B', 'S', 'G'];
$rows = [];
foreach ($order as $name) {
	if (isset($byName[$name])) {
		$rows[] = $byName[$name];
	}
}

var_dump($rows[0]);
var_dump($rows[2]);
var_dump($rows[4]);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
array(2) {
  [0]=>
  string(1) "X"
  [1]=>
  string(5) "Mixed"
}
array(2) {
  [0]=>
  string(1) "M"
  [1]=>
  string(3) "Man"
}
array(2) {
  [0]=>
  string(1) "S"
  [1]=>
  string(6) "Silver"
}
done!
