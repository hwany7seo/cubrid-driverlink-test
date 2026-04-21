--TEST--
cubrid_data_seek (ODBC: odbc_fetch_row Absolute row number)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
if (extension_loaded('cubrid')) {
	die('skip ODBC shim: unload CUBRID PHP extension to avoid cubrid_data_seek vs ODBC result handle clash');
}
?>
--XFAIL--
ODBC driver returns for odbc_fetch_row.
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

$req = odbc_exec($conn, 'SELECT id, name FROM seek_tb ORDER BY id');
if (!$req) {
	printf("[000] SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

printf("#####positive testing#####\n");
odbc_fetch_row($req, 1);
$result = [(string) odbc_result($req, 1), (string) odbc_result($req, 2)];
var_dump($result);

odbc_fetch_row($req, 2);
$result = [(string) odbc_result($req, 1), (string) odbc_result($req, 2)];
var_dump($result);

odbc_fetch_row($req, 4);
$result = [(string) odbc_result($req, 1), (string) odbc_result($req, 2)];
var_dump($result);

odbc_free_result($req);

printf("#####negative testing#####\n");
$req1 = odbc_exec($conn, 'SELECT id, name FROM seek_tb ORDER BY id');
if (!$req1) {
	printf("[000] SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

//offset is large than range
try {
	$mov1 = odbc_fetch_row($req1, 1, 1);
	if (false == $mov1) {
		printf("[001]Expect false");
	} else {
		printf("[001]Move success\n");
		printf("col\t1: %s, col2: %s\n", odbc_result($req1, 1), odbc_result($req1, 2));
	}
} catch (Error $e) {
	printf("[001]Exception: %s\n", $e->getMessage());
}

//offset is large than range
$mov2 = odbc_fetch_row($req1, 6);
if (false == $mov2) {
	printf("[002]Expect false [%d] [%s]\n", -20005, 'Invalid cursor position');
} else {
	printf("[002]Move success\n");
	printf("col\t1: %s, col2: %s\n", odbc_result($req1, 1), odbc_result($req1, 2));
}

//offset is less than 0
$mov3 = odbc_fetch_row($req1, -1);
if (false == $mov3) {
	printf("[003]Expect false [%d] [%s]\n", -20005, 'Invalid cursor position');
} else {
	printf("[003]Move success\n");
	printf("col\t1: %s, col2: %s\n", odbc_result($req1, 1), odbc_result($req1, 2));
}
odbc_free_result($req1);

$req2 = odbc_exec($conn, 'SELECT id, name FROM seek_tb WHERE id > 10 ORDER BY id');
if (!$req2) {
	printf("[000] empty SELECT failed [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}
//query result is no data
$mov4 = odbc_fetch_row($req2, 1);
if (false == $mov4) {
	printf("[004]Expect false [%d] [%s]\n", odbc_error(), odbc_errormsg());
} else {
	printf("[004]Move success\n");
	printf("col\t1: %s, col2: %s\n", odbc_result($req2, 1), odbc_result($req2, 2));
}
odbc_free_result($req2);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive testing#####
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(5) "name1"
}
array(2) {
  [0]=>
  string(1) "2"
  [1]=>
  string(5) "name2"
}
array(2) {
  [0]=>
  string(1) "4"
  [1]=>
  string(5) "name4"
}
#####negative testing#####
[001]Exception: odbc_fetch_row() expects at most 2 arguments, 3 given
[002]Expect false [-20005] [Invalid cursor position]

Warning: odbc_fetch_row(): Argument #3 ($row) must be greater than or equal to 1 in %s
[003]Expect false [-20005] [Invalid cursor position]
[004]Expect false [0] []
Finished!
