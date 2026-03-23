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
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

@odbc_exec($conn, "DROP TABLE autocommit_test");
cubrid_query('CREATE TABLE autocommit_test(a int)');
cubrid_query('INSERT INTO autocommit_test(a) VALUE(1)');

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

$req = cubrid_query('SELECT * FROM autocommit_test');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
cubrid_query('UPDATE autocommit_test SET a=2');

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

$req = cubrid_query('SELECT * FROM autocommit_test');
$res = odbc_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_query('DROP TABLE autocommit_test');

odbc_close($conn);
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

$req = cubrid_query('SELECT * FROM autocommit_test');

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
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: Error: DBMS, -493, Syntax: Unknown class "public.autocommit_test". select * from [public.autocommit_test]%s in %s on line %d
done!
