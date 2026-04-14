--TEST--
odbc_num_rows
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php');
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
require_once('table.inc');

$sql_stmt = "INSERT INTO php_cubrid_test(d) VALUES('php-test')";
$req = odbc_prepare($conn, $sql_stmt);

for ($i = 0; $i < 10; $i++) {
	odbc_execute($req);
}
odbc_commit($conn);

$del = odbc_exec($conn, "DELETE FROM php_cubrid_test WHERE d='php-test'");
var_dump(odbc_num_rows($del));
var_dump(odbc_num_rows($del));
var_dump(odbc_num_rows($del));

odbc_close($conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
int(10)
int(10)
int(10)
done!
