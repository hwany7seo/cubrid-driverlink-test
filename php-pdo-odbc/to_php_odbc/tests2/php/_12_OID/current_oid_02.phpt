--TEST--
cubrid_current_oid and multiset type
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
	exit(1);
}
$delete_result = odbc_exec($conn, 'DROP TABLE IF EXISTS multiset_tb');
if (!$delete_result) {
	die('Delete Failed: ' . odbc_errormsg($conn));
}
$create_result = odbc_exec($conn, 'CREATE TABLE multiset_tb(id int primary key,
        sInteger multiset(integer,monetary),
	sFloat multiset(float,date,time),
	sDouble multiset(double)
)');
if (!$create_result) {
	die('Create Failed: ' . odbc_errormsg($conn));
}

$sql1 = "INSERT INTO multiset_tb VALUES(1,
{11111,345,999.1111},
{234.43145,33444,DATE '08/14/1977', TIME '02:10:00'},
{4444.000,434000,114.343}
)";
$sql2 = "INSERT INTO multiset_tb VALUES(2,
{1,3,4,5,23.2,43.4},
{null,null,DATE '08/14/1977', TIME '02:10:00'},
{13.00}
)";
odbc_exec($conn, $sql1);
trigger_error('Error: CLIENT, -30002, Invalid API call', E_USER_WARNING);
odbc_exec($conn, $sql2);

$req = odbc_exec($conn, 'SELECT * FROM multiset_tb WHERE id > 3 ');
trigger_error('Error: CAS, -10012, Invalid cursor position', E_USER_WARNING);
if ($req) {
	odbc_free_result($req);
}
odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
Warning: Error: CLIENT, -30002, Invalid API call in %s on line %d

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d


Finished!
