--TEST--
cubrid_insert_id
--SKIPIF--
<?php # vim:ft=php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$id = cubrid_insert_id($conn);
var_dump($id);

@odbc_exec($conn, "DROP TABLE cubrid_test");
odbc_exec($conn, "CREATE TABLE cubrid_test (d int AUTO_INCREMENT(1, 2), e numeric(38, 0) AUTO_INCREMENT(11111111111111111111111111111111111111, 2), t varchar)");

for ($i = 0; $i < 10; $i++) {
    odbc_exec($conn, "INSERT INTO cubrid_test (t) VALUES('cubrid_test')");
}

$id = cubrid_insert_id();
var_dump($id);

odbc_exec($conn, "SELECT * FROM cubrid_test");
$id = cubrid_insert_id();
var_dump($id);

odbc_exec($conn, "CREATE TABLE cubrid_test (d int AUTO_INCREMENT(1, 2), e numeric(39, 0) AUTO_INCREMENT(1, 2), t varchar)");
@odbc_exec($conn, "DROP TABLE cubrid_test");
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
bool(false)
string(2) "19"
int(0)

Warning: Error: DBMS, -493, Syntax: Precision (39) too large. Maximum precision is 38. %s in %s on line %d
done!
