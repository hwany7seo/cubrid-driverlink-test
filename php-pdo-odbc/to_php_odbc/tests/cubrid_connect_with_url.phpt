--TEST--
cubrid_connect_with_url
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
require_once __DIR__ . '/cubrid_odbc_connect_test.inc';

$tmp = null;
/* [001] @cubrid_connect_with_url() 무인자 → NULL 기대 */
$tmp = @odbc_connect('', '', '');
if ($tmp !== false) {
	printf("[001] Expecting connection failure for empty DSN, got %s\n", gettype($tmp));
}

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[002] [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
$conn1 = odbc_connect($cubrid_odbc_dsn, '', '');
$conn2 = odbc_connect($cubrid_odbc_dsn, '', '');
printf(
	"[003] ODBC handle identity: first===second %d first===third %d\n",
	$conn === $conn1 ? 1 : 0,
	$conn === $conn2 ? 1 : 0
);
$closed = [];
foreach ([$conn, $conn1, $conn2] as $h) {
	if (!cubrid_odbc_compat_is_link($h)) {
		continue;
	}
	$k = is_object($h) ? 'o' . spl_object_id($h) : 'r' . (int) $h;
	if (isset($closed[$k])) {
		continue;
	}
	$closed[$k] = true;
	odbc_close($h);
}

print "done!\n";

$dsn5 = cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'uid', 'public_error_user');
$c = @odbc_connect($dsn5, '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[005]', $e, $m);
}
$dsn6 = cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'pwd', 'wrong_password');
$c = @odbc_connect($dsn6, '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[006]', $e, $m);
}
?>
--CLEAN--
--EXPECTF--
[003] ODBC handle identity: first===second 1 first===third 1
done!
[005] -165 [CUBRID][ODBC CUBRID Driver][-165]User "%s" is invalid.[CAS INFO-%s].
[006] -171 [CUBRID][ODBC CUBRID Driver][-171]Incorrect or missing password.[CAS INFO-%s].
