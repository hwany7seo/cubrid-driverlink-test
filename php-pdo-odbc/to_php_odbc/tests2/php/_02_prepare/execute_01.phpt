--TEST--
cubrid_execute
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS prepare_tb');
$sql = <<<EOD
CREATE TABLE prepare_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob, c11 blob);
EOD;
if(!$req=odbc_prepare($conn,$sql)){
   printf("[%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
odbc_execute($req);
odbc_exec($conn,"insert into prepare_tb values('string1','char2',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");

printf("#####error execute#####\n");
$req = odbc_prepare($conn, 'INSERT INTO prepare_tb(c1) VALUES(?)');
cubrid_bind($req, 1, 'bind test');
$tmp = false;
try {
	cubrid_execute(null);
} catch (\Throwable $e) {
	$tmp = false;
}
if (false == $tmp) {
   printf("[001] %s\n", "Undefined variable");
}else{
   printf("[001] execute success.\n");
   $select=odbc_exec($conn,"select c1 from prepare_tb");
   $result = odbc_fetch_array($select);
   var_dump($result);
}

if (false ==($tmp =odbc_exec($conn,"nothissql"))) {
   printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[002] execute success.\n");
}

$req3 = odbc_prepare($conn, "select c1,c2,c3,c4,c5,c6,c7,c8,c9 from prepare_tb where c1 like ? ");
cubrid_bind($req3, 1, 'string%');
if (false ==($tmp =cubrid_execute($req3))) {
   printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[003] execute success.\n");
   $result = odbc_fetch_array($req3);
   $result['c8'] = strtoupper(bin2hex($result['c8']));
   var_dump($result);
}

$req3 = odbc_prepare($conn, "select * from prepare_tb where c4=? ");
cubrid_bind($req3, 1, 11.11);
if (false ==($tmp =cubrid_execute($req3))) {
   printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[004] execute success.\n");
   $select=odbc_exec($conn,"select * from prepare_tb");
   $result = odbc_fetch_array($select);
   $result['c8'] = strtoupper(bin2hex($result['c8']));
   if (isset($result['c11']) && is_string($result['c11'])) {
	$result['c11'] = strtoupper(bin2hex($result['c11']));
   }
   var_dump($result);
}

$clr = odbc_exec($conn, 'SELECT c1 FROM prepare_tb LIMIT 1');
if ($clr) {
	odbc_free_result($clr);
}
$tmp = false;
try {
	odbc_exec(null, 'SELECT 1');
} catch (\Throwable $e) {
	$tmp = false;
}
printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));

odbc_close($conn);
print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####error execute#####
[001] Undefined variable

Warning: odbc_exec(): SQL error: [CUBRID][ODBC CUBRID Driver][-493]Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '(' [CAS INFO-192.168.3.31:33000,1,2077629]., SQL state S1000 in SQLExecDirect in /home/hwanyseo/source/fork/cubrid-driverlink-test/php-pdo-odbc/to_php_odbc/tests2/php/_02_prepare/execute_01.php on line 33
[002] [0] [CUBRID][ODBC CUBRID Driver][-493]Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '(' [CAS INFO-192.168.3.31:33000,1,2077629].
[003] execute success.
array(9) {
  ["c1"]=>
  string(7) "string1"
  ["c2"]=>
  string(20) "char2               "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.1099999999999994"
  ["c5"]=>
  string(8) "02:10:00"
  ["c6"]=>
  string(10) "1977-08-14"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  ["c8"]=>
  string(2) "80"
  ["c9"]=>
  string(11)%r[\s\S]*?(?=\n\})%r
}
[004] execute success.
array(11) {
  ["c1"]=>
  string(7) "string1"
  ["c2"]=>
  string(20) "char2               "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.1099999999999994"
  ["c5"]=>
  string(8) "02:10:00"
  ["c6"]=>
  string(10) "1977-08-14"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  ["c8"]=>
  string(2) "80"
  ["c9"]=>
  string(11)%r[\s\S]*?(?=\n  \["c10"\]=>)%r
  ["c10"]=>
  string(13) "This is a Dog"
  ["c11"]=>
  string(6) "000001"
}
[005] [0] [CUBRID][ODBC CUBRID Driver][-493]Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '(' [CAS INFO-192.168.3.31:33000,1,2077629].
Finished!
