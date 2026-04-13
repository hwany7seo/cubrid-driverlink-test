--TEST--
cubrid_drop and table contains partiton
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
printf("negative testing\n");

include_once('connect.inc');
$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS partition_tb');
odbc_exec($conn, 'CREATE TABLE partition_tb(id int ,test_char char(50),test_varchar varchar(2000))');
$alterSql = "ALTER TABLE partition_tb PARTITION BY LIST (test_char) (PARTITION p0 VALUES IN ('aaa','bbb','ddd'),PARTITION p1 VALUES IN ('fff','ggg','hhh',NULL),PARTITION p2 VALUES IN ('kkk','lll','mmm') )";
$insertSql = "INSERT INTO partition_tb VALUES(1,'aaa','aaa')";
$insertSql2 = "INSERT INTO partition_tb VALUES(5,'ggg','ggg')";
odbc_exec($conn, $alterSql);
odbc_exec($conn, $insertSql);
odbc_exec($conn, $insertSql2);

$req = odbc_exec($conn, 'SELECT * FROM partition_tb WHERE id > 10 ');

trigger_error('Error: CAS, -10012, Invalid cursor position', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-10012, 'Invalid cursor position'];
$oid = cubrid_current_oid($req);
if (false == $oid) {
	printf("Expect false for oid [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
} else {
	printf("oid: %s\n", $oid);
}
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

$tmp = cubrid_drop($conn, $oid);
if (false == $tmp) {
	printf("Expect false for cubrid_drop, [%d] [%s] \n", cubrid_errno($conn), cubrid_error($conn));
} else {
	printf("drop success\n");
}

$tmp2 = cubrid_drop($conn, $nothisoid);
if (false == $tmp2) {
	printf("[002]Expect false for cubrid_drop, [%d] [%s] \n", cubrid_errno($conn), cubrid_error($conn));
} else {
	printf("drop success\n");
}

if ($req) {
	odbc_free_result($req);
}

odbc_close($conn);

print "Fished!\n";
?>
--CLEAN--
--EXPECTF--
negative testing

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d
Expect false for oid [-10012] Invalid cursor position

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
Expect false for cubrid_drop, [-20020] [Invalid oid string] 

Notice: Undefined variable: nothisoid in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
[002]Expect false for cubrid_drop, [-20020] [Invalid oid string] 
Fished!
