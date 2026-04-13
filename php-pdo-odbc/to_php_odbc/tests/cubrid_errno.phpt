--TEST--
cubrid_errno
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";

$tmp    = NULL;
$link   = NULL;

if (false !== ($tmp = @cubrid_errno())) {
	printf("[001] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

if (null !== ($tmp = @cubrid_errno($link))) {
	printf("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!is_null($tmp = @cubrid_errno($link, 'too many args'))) {
	printf("[002b] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!$conn = odbc_connect($cubrid_odbc_dsn, "", "")) {
	printf("[003] Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
}
var_dump(cubrid_errno($conn));

odbc_exec($conn, 'SELECT * FROM code');
var_dump(cubrid_errno($conn));

odbc_close($conn);

var_dump(cubrid_errno($conn));

if (!$conn2 = odbc_connect($cubrid_odbc_dsn, "", "")) {
	printf("[003] Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
}
var_dump(cubrid_errno($conn2));
odbc_exec($conn2, 'SELECT * FROM table_unknow');

printf("cubrid_error: %s\n", cubrid_error($conn2));
printf("odbc_error: %s\n", odbc_error($conn2));
printf("odbc_errormsg: %s\n", odbc_errormsg($conn2));

var_dump(cubrid_errno());

print "done!";
?>
--CLEAN--
--EXPECTF--
int(0)
int(0)

Warning: cubrid_errno(): supplied resource is not a valid CUBRID Connect resource in %s on line %d
bool(false)
int(0)

Warning: %s
cubrid_error: %s
odbc_error: %s
odbc_errormsg: %s
bool(false)
done!
