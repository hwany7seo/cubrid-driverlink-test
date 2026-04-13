--TEST--
cubrid_autocommit
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

@odbc_exec($conn, "DROP TABLE if exists commit3_tb");
odbc_exec($conn, 'CREATE TABLE commit3_tb(a int)');
odbc_exec($conn, 'INSERT INTO commit3_tb(a) VALUE(1)');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$req = odbc_exec($conn, 'SELECT * FROM commit3_tb');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.\n");
}
odbc_exec($conn, 'UPDATE commit3_tb SET a=2');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$req = odbc_exec($conn, 'SELECT * FROM commit3_tb');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

odbc_exec($conn, 'DROP TABLE commit3_tb');

odbc_close($conn);
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$req = odbc_exec($conn, 'SELECT * FROM commit3_tb');

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
array(1) {
  ["a"]=>
  string(1) "1"
}
Autocommit is OFF.
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: Error: DBMS, -493, Syntax: Unknown class "dba.commit3_tb". select * from [dba.commit3_tb]%s in %s on line %d
done!
