--TEST--
cubrid_fetch_lengths
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php');
?>
--FILE--
<?php
/**
 * cubrid_next_result / 한 번의 odbc_exec 에 다중 SQL(CUBRID_EXEC_QUERY_ALL) 은 CUBRID CCI 전용에 가깝고,
 * 표준 ODBC+unixODBC 에서는 보장되지 않는다. 단일 결과 집합으로 fetch_lengths 동작만 검증한다.
 */
include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);
if (!$conn) {
	printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
	exit(1);
}

require_once('table.inc');

@odbc_exec($conn, "INSERT INTO php_cubrid_test(a,d) VALUES (1, 'char1'), (2, 'varchar22')");

if (!$req = odbc_exec($conn, "SELECT * FROM php_cubrid_test ORDER BY a")) {
	printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
	exit(1);
}

if (!odbc_fetch_row($req)) {
	print "no row\n";
	exit(1);
}
printf("The first row: %s %s\n", odbc_result($req, 1), trim((string) odbc_result($req, 4)));

$lens = cubrid_fetch_lengths($req);
printf("Field lengths: ");
for ($i = 0; $i < 6; $i++) {
	printf("%d ", $lens[$i] ?? 0);
}
printf("\n");

if (!odbc_fetch_row($req)) {
	print "no row2\n";
	exit(1);
}
printf("The second row: %s %s\n", odbc_result($req, 1), trim((string) odbc_result($req, 4)));

$lens = cubrid_fetch_lengths($req);
printf("Field lengths: ");
for ($i = 0; $i < 6; $i++) {
	printf("%d ", $lens[$i] ?? 0);
}
printf("\n");

odbc_free_result($req);

$req2 = odbc_exec($conn, "SELECT s_name, f_name FROM code WHERE s_name = 'X'");
if (!$req2) {
	printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
	exit(1);
}
if (!odbc_fetch_row($req2)) {
	print "no code row\n";
	exit(1);
}
printf("\nThe third row: %s %s\n", odbc_result($req2, 1), odbc_result($req2, 2));

$lens = cubrid_fetch_lengths($req2);
printf("Field lengths: ");
for ($i = 0; $i < 2; $i++) {
	printf("%d ", $lens[$i] ?? 0);
}
printf("\n");

odbc_free_result($req2);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
The first row: 1 char1
Field lengths: 1 0 0 30 0 0 
The second row: 2 varchar22
Field lengths: 1 0 0 30 0 0 

The third row: X Mixed
Field lengths: 1 5 
done!
