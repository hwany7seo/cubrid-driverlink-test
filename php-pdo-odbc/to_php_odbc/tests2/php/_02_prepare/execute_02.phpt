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
Array
(
    [date '2002-01-01'-datetime '2001-02-02 12:00:00 am'] => 28771200000
)
Array
(
    [date '2002-01-01'+'10'] => 2002-01-11
)
Array
(
    [4+'5.2'] => 9.1999999999999993
)
Array
(
    [date '2002-01-01'+1] => 2002-01-02
)
Array
(
    ['1'+'1'] => 11
)
Array
(
    ['3'*'2'] => 6.0000000000000000
)
Array
(
    [bit_length('CUBRID')] => 48
)
Array
(
    [bit_length(B'010101010')] => 9
)
Array
(
    [char_length('')] => 0
)
Array
(
    [chr(68 using iso88591)|| chr(68-2 using iso88591)] => DB
)
Array
(
    [concat('CUBRID', '2008', 'R3.0', null)] => 
)
Array
(
    [instr('12345abcdeabcde', 'b', -1)] => 12
)
Array
(
    [(( cast({3, 3, 3, 2, 2, 1} as set))+( cast({4, 3, 3, 2} as multiset)))] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 2
            [3] => 3
            [4] => 3
            [5] => 3
            [6] => 4
        )

)
Array
(
    [( cast(timestamp '2008-12-25 10:30:20' as time))] => 10:30:20
)

Warning: Error: DBMS, -427, Data overflow on data type "character".%s in %s on line %d
[-427] Data overflow on data type "character".%s
Finished!
