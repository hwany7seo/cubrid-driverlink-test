--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include 'connect.inc';
$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS current_oid2_tb');
odbc_exec($conn, 'CREATE TABLE current_oid2_tb(a int, b varchar(10) ) DONT_REUSE_OID');
odbc_exec($conn, "INSERT INTO current_oid2_tb VALUES(1,'varchar1'),(2,'varchar2'),(3,'varchar3'),(4,'varchar4')");

$r = odbc_exec($conn, "SELECT b FROM current_oid2_tb WHERE a = 3");
if ($r && odbc_fetch_row($r)) {
	$v = (string) odbc_result($r, 1);
	odbc_free_result($r);
	printf("The third record's oid: %s\n", $v);
	var_dump($v);
}

$r2 = odbc_exec($conn, "SELECT b FROM current_oid2_tb WHERE a = 4");
if ($r2 && odbc_fetch_row($r2)) {
	$v2 = (string) odbc_result($r2, 1);
	odbc_free_result($r2);
	printf("\n\nThe fourth record's oid: %s\n", $v2);
	var_dump($v2);
}

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
The third record's oid: %s
string(8) "varchar3"


The fourth record's oid: %s
string(8) "varchar4"
Finished!
