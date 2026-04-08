--TEST--
odbc_exec
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
cubrid_odbc_set_last_connection($conn);

if (false !== ($tmp = odbc_exec($conn, 'THIS IS NOT SQL'))) {
	printf("[003] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

if (!($req = odbc_exec($conn, 'SELECT * FROM code'))) {
	printf('[004] [%s] %s\n', odbc_error($conn), odbc_errormsg($conn));
}

while ($res = odbc_fetch_array($req, CUBRID_NUM)) {
	var_dump($res);
}

odbc_free_result($req);

if (!$stmt = odbc_prepare($conn, "SELECT * FROM code WHERE s_name = ?")) {
	printf('[005] [%s] %s\n', odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

if (false !== ($tmp = odbc_execute($stmt))) {
	printf('[006] Expecting boolean/false for unbound placeholder, got %s/%s\n', gettype($tmp), $tmp);
}

if (!$stmt2 = odbc_prepare($conn, "SELECT * FROM code WHERE s_name='M'")) {
	printf('[007] [%s] %s\n', odbc_error($conn), odbc_errormsg($conn));
	exit(1);
}

if (!odbc_execute($stmt2)) {
	printf('[008] [%s] %s\n', odbc_error($conn), odbc_errormsg($conn));
}

while ($array = odbc_fetch_array($stmt2)) {
	var_dump($array);
}

odbc_free_result($stmt2);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: odbc_exec(): SQL error: [CUBRID][ODBC CUBRID Driver][-493]Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' [%s]., SQL state S1000 in SQLExecDirect in %s
array(2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
array(2) {
  ["s_name"]=>
  string(1) "W"
  ["f_name"]=>
  string(5) "Woman"
}
array(2) {
  ["s_name"]=>
  string(1) "M"
  ["f_name"]=>
  string(3) "Man"
}
array(2) {
  ["s_name"]=>
  string(1) "B"
  ["f_name"]=>
  string(6) "Bronze"
}
array(2) {
  ["s_name"]=>
  string(1) "S"
  ["f_name"]=>
  string(6) "Silver"
}
array(2) {
  ["s_name"]=>
  string(1) "G"
  ["f_name"]=>
  string(4) "Gold"
}

Warning: odbc_execute(): Not enough parameters (0 should be 1) given in %s
array(2) {
  ["s_name"]=>
  string(1) "M"
  ["f_name"]=>
  string(3) "Man"
}
done!
