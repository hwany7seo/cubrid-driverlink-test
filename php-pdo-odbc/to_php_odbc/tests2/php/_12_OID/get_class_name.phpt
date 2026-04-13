--TEST--
cubrid_get_class_name
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
	printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS class_name_tb');
odbc_exec($conn, "CREATE TABLE class_name_tb(id int, name varchar(10)) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO class_name_tb VALUES(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

$fq = null;
$rq = @odbc_exec($conn, "SELECT LOWER(TRIM(unique_name)) AS fq FROM _db_class WHERE class_name = 'class_name_tb'");
if ($rq && odbc_fetch_row($rq)) {
	$fq = (string) odbc_result($rq, 1);
	odbc_free_result($rq);
}
if ($fq === null || $fq === '') {
	$fq = strtolower($user) . '.class_name_tb';
}

print_r($fq);

print "\n";
print 'done!';
?>
--CLEAN--
--EXPECTF--
%s.class_name_tb
done!
