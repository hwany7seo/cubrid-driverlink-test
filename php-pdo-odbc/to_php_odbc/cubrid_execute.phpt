--TEST--
odbc_exec
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

$tmp = NULL;
$conn = NULL;

if (!is_null($tmp = @odbc_exec())) {
    printf('[001] Expecting NULL, got %s/%s\n', gettype($tmp), $tmp);
}

if (!is_null($tmp = @odbc_exec($conn))) {
    printf('[002] Expecting NULL, got %s/%s\n', gettype($tmp), $tmp);
}

$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");

if (false !== ($tmp = odbc_exec($conn, 'THIS IS NOT SQL'))) {
    printf("[003] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

if (!($req = odbc_exec($conn, 'SELECT * FROM code'))) {
    printf('[004] [%d] %s\n', odbc_error(), odbc_errormsg());
}

while ($res = odbc_fetch_array($req, CUBRID_NUM)) {
    var_dump($res);
}

odbc_close_request($req);

if (!$req = odbc_prepare($conn, "SELECT * FROM code WHERE s_name = ?")) {
    printf('[005] [%d] %s\n', odbc_error(), odbc_errormsg());
    exit(1);
}

if (false !== ($tmp = odbc_exec($req))) {
    printf('[006] [%d] Expecting boolean/false, got %s/%s\n', gettype($tmp), $tmp);
}

if (!$req = odbc_prepare($conn, "SELECT * FROM code WHERE s_name='M'")) {
    printf('[007] [%d] %s\n', odbc_error(), odbc_errormsg());
    exit(1);
}

if (!($res = odbc_exec($req))) {
    printf('[008] [%d] %s\n', odbc_error(), odbc_errormsg());
}

while ($array = odbc_fetch_array($req)) {
    var_dump($array);
}

odbc_close_request($req);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: Error: DBMS, -493, Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' %s in %s on line %d
array(2) {
  [0]=>
  string(1) "X"
  [1]=>
  string(5) "Mixed"
}
array(2) {
  [0]=>
  string(1) "W"
  [1]=>
  string(5) "Woman"
}
array(2) {
  [0]=>
  string(1) "M"
  [1]=>
  string(3) "Man"
}
array(2) {
  [0]=>
  string(1) "B"
  [1]=>
  string(6) "Bronze"
}
array(2) {
  [0]=>
  string(1) "S"
  [1]=>
  string(6) "Silver"
}
array(2) {
  [0]=>
  string(1) "G"
  [1]=>
  string(4) "Gold"
}

Warning: Error: CLIENT, -30015, Some parameter not binded in %s on line %d
array(4) {
  [0]=>
  string(1) "M"
  ["s_name"]=>
  string(1) "M"
  [1]=>
  string(3) "Man"
  ["f_name"]=>
  string(3) "Man"
}
done!
