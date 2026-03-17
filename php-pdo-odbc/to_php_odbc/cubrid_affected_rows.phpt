--TEST--
odbc_num_rows
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php')
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");

require_once('table.inc');

$sql_stmt = "INSERT INTO php_cubrid_test(d) VALUES('php-test')";
$req = odbc_prepare($conn, $sql_stmt);

for ($i = 0; $i < 10; $i++) {
    odbc_exec($req);
}
odbc_commit($conn);

$req = odbc_exec($conn, "DELETE FROM php_cubrid_test WHERE d='php-test'", CUBRID_ASYNC);
var_dump(odbc_num_rows());
var_dump(odbc_num_rows($conn));
var_dump(odbc_num_rows($req));

cubrid_disconnect($conn);

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
