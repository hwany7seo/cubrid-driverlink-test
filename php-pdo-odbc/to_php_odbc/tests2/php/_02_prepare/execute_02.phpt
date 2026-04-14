--TEST--
cubrid_execute: sql statements are about calculate
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

printf("#####calculate result#####\n");
//Date calculate
$result =odbc_exec($conn," select date'2002-01-01' - datetime'2001-02-02 12:00:00 am';");
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT date'2002-01-01' + '10';" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn, "SELECT 4 + '5.2'");
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn, "SELECT DATE'2002-01-01'+1;");
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT '1'+'1';" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn, "SELECT '3'*'2';");
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

//LENGTH calculate
$result =odbc_exec($conn,"select BIT_LENGTH('CUBRID');" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"select BIT_LENGTH(B'010101010');" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT LENGTH('');" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT CHR(68) || CHR(68-2);" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT CONCAT('CUBRID', '2008' , 'R3.0',NULL)" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT INSTR ('12345abcdeabcde','b', -1);" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT ((CAST ({3,3,3,2,2,1} AS SET))+(CAST ({4,3,3,2} AS MULTISET)));" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =odbc_exec($conn,"SELECT (CAST(TIMESTAMP'2008-12-25 10:30:20' AS TIME));" );
$row = odbc_fetch_array($result, CUBRID_ASSOC);
print_r($row);

if(!$result =odbc_exec($conn,"SELECT (CAST(1234.567890 AS CHAR(5)));" )){
   printf("[%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   $row = odbc_fetch_array($result, CUBRID_ASSOC);
   print_r($row);
}

odbc_close($conn);
print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####calculate result#####
%A
Finished!
