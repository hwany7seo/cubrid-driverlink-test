--TEST--
odbc_execute
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php')
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

@odbc_exec($conn, 'DROP TABLE bind_test');
odbc_exec($conn, 'CREATE TABLE bind_test(c1 varchar(10))');

$req = odbc_prepare($conn, 'INSERT INTO bind_test(c1) VALUES(?)');

odbc_execute($req, array(null));

odbc_execute($req, array('1234'));

odbc_execute($req, array(null));

$req = odbc_exec($conn, "SELECT * FROM bind_test");
while ($row = odbc_fetch_array($req)) {
	if ($row["c1"]) {
		printf("%s\n", $row["c1"]);
	} else {
		printf("NULL\n");
	}
}

print 'done!';
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
NULL
1234
NULL
done!
