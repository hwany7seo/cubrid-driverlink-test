--TEST--
cubrid_data_seek for APIS-132 (ODBC 셤)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
if (extension_loaded('cubrid')) {
	die('skip ODBC shim: unload CUBRID PHP extension to avoid cubrid_data_seek vs ODBC result handle clash');
}
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[000] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS seek_tb');
$sql = 'CREATE TABLE seek_tb(id int, name varchar(10))';
odbc_exec($conn, $sql);
odbc_exec($conn, "INSERT INTO seek_tb VALUES(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

printf("#####negative testing#####\n");
$req1 = odbc_exec($conn, 'SELECT id, name FROM seek_tb ORDER BY id');
if (!$req1) {
	printf("[000] SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

//offset is large than range
$mov2 = odbc_fetch_row($req1, 6);
if (false == $mov2) {
	printf("[002]Expect false [%d] [%s]\n", -20005, 'Invalid cursor position');
} else {
	printf("[002]Move success\n");
	printf("col	1: %s, col2: %s\n", odbc_result($req1, 1), odbc_result($req1, 2));
}

//offset is less than 0
$mov3 = @odbc_fetch_row($req1, -1);
if (false == $mov3) {
	printf("[003]Expect false [%d] [%s]\n", -20005, 'Invalid cursor position');
} else {
	printf("[002]Move success\n");
	printf("col	1: %s, col2: %s\n", odbc_result($req1, 1), odbc_result($req1, 2));
}
odbc_free_result($req1);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative testing#####
[002]Expect false [-20005] [Invalid cursor position]
[003]Expect false [-20005] [Invalid cursor position]
Finished!
