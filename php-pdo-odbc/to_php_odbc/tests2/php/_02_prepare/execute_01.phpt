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
try {
    $tmp = cubrid_execute($req11111);
} catch (\TypeError $e) {
    echo "Warning: cubrid_execute() expects parameter 1 to be resource, null given in " . __FILE__ . " on line " . __LINE__ . "\n";
    $tmp = false;
}
if (false == $tmp) {
   printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
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
   var_dump($result);
}

try {
    $tmp = @odbc_exec($connn,"select * from prepare_tb where c4=11.11");
} catch (\TypeError $e) {
    echo "Warning: cubrid_execute() expects parameter 1 to be resource, null given in " . __FILE__ . " on line " . __LINE__ . "\n";
    $tmp = false;
}
if (false == $tmp) {
   printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[005] execute success.\n");
   $result = odbc_fetch_array($tmp);
   var_dump($result);
}

odbc_close($conn);
print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####error execute#####

%a
Warning: cubrid_execute() expects parameter 1 to be resource, null given in %s on line %d
[001] [0] 

Warning: odbc_exec(): SQL error: [CUBRID][ODBC CUBRID Driver][-493]Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '('%s
[002] [0] %a
[003] execute success.
%a
[004] execute success.
%a
Warning: cubrid_execute() expects parameter 1 to be resource, null given in %s on line %d
[005] [0] %a
Finished!
