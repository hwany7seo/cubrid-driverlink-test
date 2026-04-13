--TEST--
cubrid_prepare cubrid_bind
--SKIPIF--
<?php
require_once("skipif.inc");
require_once("skipifconnectfailure.inc");
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

odbc_exec($conn, 'DROP TABLE IF EXISTS bind_tb');
$sql = <<<EOD
CREATE TABLE bind_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob, c11 blob);
EOD;

if(!$req=odbc_prepare($conn,$sql)){
   printf("[%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
odbc_execute($req);

$req = odbc_prepare($conn, 'INSERT INTO bind_tb(c1) VALUES(?)');
printf("#####error bind#####\n");
if (!is_null($tmp = cubrid_bind($req, 0, 'bind test'))) {
   printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[001] bind success.\n");
}
if (false == ($tmp =cubrid_bind($req,99,'bind test'))) {
   printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[002] bind success.\n");
}

/*
if (false == ($tmp =cubrid_bind($req, 1,bind test))) {
   printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
*/

$req4 = odbc_prepare($conn, 'INSERT INTO bind_tb(c3) VALUES(?)');
if (false == ($tmp =cubrid_bind($req4,1,2147483648,'number'))) {
   printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[004] bind success.\n");
}
if (false ==($tmp =cubrid_execute($req4))) {
   printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[004] execute success.\n");
   $result = odbc_fetch_array($req4);
   var_dump($result);
}

$req5 = odbc_prepare($conn, 'INSERT INTO bind_tb(c3) VALUES(?)');
if (false == ($tmp =cubrid_bind($req5,1,'1233','number'))) {
   printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[005] bind success.\n");
}
if (false ==($tmp =cubrid_execute($req5))) {
   printf("[005] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[005] execute success.\n");
   $select=odbc_exec($conn,"select c3 from bind_tb");
   $result = odbc_fetch_array($select);
   var_dump($result);
}

$req6= odbc_prepare($conn, 'INSERT INTO bind_tb(c4) VALUES(?)');
if (false == ($tmp =cubrid_bind($req6,1,NULL))) {
   printf("[006] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[006] bind success.\n");
}
if (false ==($tmp =cubrid_execute($req6))) {
   printf("[006] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[006] execute success.\n");
   $select=odbc_exec($conn,"select c4 from bind_tb");
   $result = odbc_fetch_array($select);
   var_dump($result);
}

/*
$req7= odbc_prepare($conn, 'INSERT INTO bind_tb(c8) VALUES(?)');
if (false == ($tmp =cubrid_bind($req7,1,'1010','bit'))) {
   printf("[007] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[007] bind success.\n");
}
if (false ==($tmp =cubrid_execute($req7))) {
   printf("[007] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[007] execute success.\n");
   $select=odbc_exec($conn,"select c8 from bind_tb");
   $result = odbc_fetch_array($select);
   var_dump($result);
}
*/

$req8= odbc_prepare($conn, 'INSERT INTO bind_tb(c3) VALUES(?)');
if (false == ($tmp =cubrid_bind($req8,1,222,'inttttt'))) {
   printf("[008] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[008] bind success.\n");
}


odbc_close($conn);

print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####error bind#####
[001] [0] 
[002] [0] 
[004] bind success.

Warning: Error: DBMS, -494, Semantic: Cannot coerce host var to type integer. %s in %s on line %d
[004] [-494] Semantic: Cannot coerce host var to type integer. %s
[005] bind success.
[005] execute success.
array(1) {
  ["c3"]=>
  string(4) "1233"
}
[006] bind success.
[006] execute success.
array(1) {
  ["c4"]=>
  NULL
}

[008] [0] 
Finished!
