--TEST--
cubrid_seq_drop
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only OID API — Not supported by PHP ODBC");
?>
--FILE--
<?php
/**
 * cubrid_seq_drop(conn, oid, "c", 4) 대신 UPDATE 로 LIST 를 {11,22,33} 으로 맞춤.
 */
include_once('connect.inc');
require_once __DIR__ . '/../../cubrid_odbc_collection.inc';

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
@odbc_exec($conn, 'DROP TABLE seq_drop_tb');
if (!odbc_exec($conn, 'CREATE TABLE seq_drop_tb (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID')) {
	printf("[002] CREATE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_exec($conn, "INSERT INTO seq_drop_tb(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')")) {
	printf("[003] INSERT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$req = odbc_exec($conn, 'SELECT c FROM seq_drop_tb WHERE a = 1');
if (!$req || !odbc_fetch_row($req)) {
	printf("[004] SELECT failed\n");
	exit(1);
}
$raw = odbc_result($req, 1);
odbc_free_result($req);

$attr = cubrid_odbc_normalize_list_column($raw);
if ($attr === null) {
	printf("[005] Unexpected LIST form\n");
	exit(1);
}
var_dump($attr);

if (!odbc_exec($conn, 'UPDATE seq_drop_tb SET c = {11, 22, 33} WHERE a = 1')) {
	printf("[006] UPDATE failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
if (!odbc_commit($conn)) {
	printf("[007] commit failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

$req = odbc_exec($conn, 'SELECT c FROM seq_drop_tb WHERE a = 1');
if (!$req || !odbc_fetch_row($req)) {
	printf("[008] Second SELECT failed\n");
	exit(1);
}
$raw2 = odbc_result($req, 1);
odbc_free_result($req);

$attr = cubrid_odbc_normalize_list_column($raw2);
if ($attr === null) {
	printf("[009] Unexpected LIST after UPDATE\n");
	exit(1);
}
var_dump($attr);

odbc_close($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
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
array(3) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
}
done!
