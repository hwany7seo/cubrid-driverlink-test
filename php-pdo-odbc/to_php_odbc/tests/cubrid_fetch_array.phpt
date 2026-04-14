--TEST--
odbc_fetch_array
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

$tmp = null;
$conn = null;

try {
	odbc_fetch_array();
	printf("[001] Expected error for zero args\n");
} catch (ArgumentCountError|TypeError $e) {
	/* error */
}

$tmp = false;
try {
	$tmp = odbc_fetch_array($conn);
} catch (Throwable $e) {
	$tmp = false;
}
if ($tmp !== false) {
	printf("[002] Expecting false for non-result handle, got %s/%s\n", gettype($tmp), var_export($tmp, true));
}

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[003] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
if (!$req = odbc_exec($conn, 'SELECT s_name, f_name FROM code')) {
	printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
	exit(1);
}

$byName = [];
while (($array = odbc_fetch_array($req)) !== false) {
	$lc = array_change_key_case($array, CASE_LOWER);
	$sn = $lc['s_name'] ?? null;
	if ($sn !== null) {
		$byName[(string) $sn] = $lc;
	}
}
odbc_free_result($req);

$order = ['X', 'W', 'M', 'B', 'S', 'G'];
foreach ($order as $name) {
	if (isset($byName[$name])) {
		var_dump($byName[$name]);
	}
}

var_dump(array_values($byName['X']));

var_dump($byName['W']);

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
array(2) {
  ["s_name"]=>
  string(1) "W"
  ["f_name"]=>
  string(5) "Woman"
}
array(2) {
  ["s_name"]=>
  string(1) "M"
  ["f_name"]=>
  string(3) "Man"
}
array(2) {
  ["s_name"]=>
  string(1) "B"
  ["f_name"]=>
  string(6) "Bronze"
}
array(2) {
  ["s_name"]=>
  string(1) "S"
  ["f_name"]=>
  string(6) "Silver"
}
array(2) {
  ["s_name"]=>
  string(1) "G"
  ["f_name"]=>
  string(4) "Gold"
}
array(2) {
  [0]=>
  string(1) "X"
  [1]=>
  string(5) "Mixed"
}
array(2) {
  ["s_name"]=>
  string(1) "W"
  ["f_name"]=>
  string(5) "Woman"
}
done!
