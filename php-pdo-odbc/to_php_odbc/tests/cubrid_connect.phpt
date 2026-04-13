--TEST--
cubrid_connect
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
/**
 * cubrid-php tests_74/cubrid_connect.phpt ODBC 변환.
 *
 * - [001] cci cubrid_connect() 무인자 → ODBC 에서는 빈 DSN 연결 실패에 대응.
 * - [003]/[004] cci 의 new_link=FALSE 동일 핸들 vs TRUE 신규 연결은 ODBC 에서 보장되지 않음.
 *   동일 DSN 으로 odbc_connect 를 여러 번 호출하면 보통 매번 다른 핸들(객체)이 나온다.
 * - [007]–[010], [005]–[006] 는 DSN 의 DB_NAME / uid / pwd / server 만 바꿔 재현한다.
 *   메시지·코드는 Driver Manager + CUBRID ODBC 형식이라 cci 와 문자열이 다를 수 있다.
 */
include_once('connect.inc');
require_once __DIR__ . '/cubrid_odbc_connect_test.inc';

$tmp = null;
/* [001] cci: @cubrid_connect() 무인자 → NULL 기대 */
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
/*
 * cci: new_link=false 이면 첫 두 연결이 동일, true 이면 별도 연결.
 * ODBC/PHP 8.4 는 구현에 따라 동일 Odbc\Connection 이 재사용될 수 있어 === 가 참일 수 있다.
 * 같은 인스턴스에 odbc_close 를 두 번 호출하면 예외가 나므로, 닫기는 인스턴스당 한 번만 한다.
 */
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

/* [007] 잘못된 DB */
$c = @odbc_connect(cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'DB_NAME', 'invalid_db'), '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[007]', $e, $m);
}

/* [008] 잘못된 비밀번호 */
$c = @odbc_connect(cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'pwd', '222'), '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[008]', $e, $m);
}

/* [009] 동일 호스트·닫힌 포트 — 원격 블랙홀 IP 는 타임아웃으로 run-tests 가 오래 걸릴 수 있음 */
$c = @odbc_connect(cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'port', '1'), '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[009]', $e, $m);
}

/* [010] 잘못된 사용자 */
$c = @odbc_connect(cubrid_connect_test_dsn_kv($cubrid_odbc_dsn, 'uid', 'invalid_user'), '', '');
if ($c === false) {
	[$e, $m] = cubrid_odbc_compat_odbc_errno_msg(null);
	cubrid_connect_test_err_line('[010]', $e, $m);
}

print "done!\n";

/* 원본 두 번째 블록: URL+잘못된 사용자 / 비밀번호 → DSN 변형 */
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
[007] -677 [CUBRID][ODBC CUBRID Driver][-677]Failed to connect to database server, '%s', on the following host(s): %s.
[008] -171 [CUBRID][ODBC CUBRID Driver][-171]Incorrect or missing password.[%s].
[009] -20016 [CUBRID][ODBC CUBRID Driver][-20016]Cannot connect to CUBRID CAS
[010] -165 [CUBRID][ODBC CUBRID Driver][-165]User "%s" is invalid.[%s].
done!
[005] -165 [CUBRID][ODBC CUBRID Driver][-165]User "%s" is invalid.[%s].
[006] -171 [CUBRID][ODBC CUBRID Driver][-171]Incorrect or missing password.[%s].