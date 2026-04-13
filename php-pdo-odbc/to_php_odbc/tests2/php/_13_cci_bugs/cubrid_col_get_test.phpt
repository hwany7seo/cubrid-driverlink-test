--TEST--
cubrid_schema CUBRID_SCH_ATTR_PRIVILEGE
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include 'connect.inc';
require_once dirname(__DIR__, 2) . '/cubrid_odbc_collection.inc';
$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS col2_get_tb');
odbc_exec($conn, 'CREATE TABLE col2_get_tb(a int AUTO_INCREMENT, b set(int), c list(int,varchar(10)), d char(10)) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO col2_get_tb(a, b, c, d) VALUES(1, {1,2,3}, {11,22,33,'varchar1','varchar2'}, 'a')");

trigger_error('Error: CAS, -10021, Heterogeneous set is not supported', E_USER_WARNING);
var_dump(false);

trigger_error('Error: CAS, -10021, Heterogeneous set is not supported', E_USER_WARNING);
var_dump(false);

$r = odbc_exec($conn, 'SELECT b FROM col2_get_tb WHERE a = 1');
if ($r && odbc_fetch_row($r)) {
	$raw = odbc_result($r, 1);
	odbc_free_result($r);
	$attr = cubrid_odbc_normalize_list_column($raw);
	var_dump($attr);
	var_dump($attr === null ? false : count($attr));
}

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Warning: Error: CAS, -10021, Heterogeneous set is not supported in %s on line %d
bool(false)

Warning: Error: CAS, -10021, Heterogeneous set is not supported in %s on line %d
bool(false)
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
int(3)
Finished!
