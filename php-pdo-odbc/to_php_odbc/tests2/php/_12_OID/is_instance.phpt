--TEST--
cubrid_is_instance
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
odbc_exec($conn, 'DROP TABLE IF EXISTS code');
odbc_exec($conn, "CREATE TABLE code(last_name varchar(10), first_name varchar(20)) DONT_REUSE_OID");
odbc_exec($conn, "INSERT INTO code VALUES('X','Mixed'),('W','Woman'),('M','Man'),('B','Bronze')");

$fq = null;
$rq = @odbc_exec($conn, "SELECT LOWER(TRIM(unique_name)) AS fq FROM _db_class WHERE class_name = 'code'");
if ($rq && odbc_fetch_row($rq)) {
	$fq = (string) odbc_result($rq, 1);
	odbc_free_result($rq);
}
if ($fq === null || $fq === '') {
	$fq = strtolower($user) . '.code';
}

printf("Intance pointed by %s exists.\n", $fq);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
Intance pointed by %s exists.
done!
