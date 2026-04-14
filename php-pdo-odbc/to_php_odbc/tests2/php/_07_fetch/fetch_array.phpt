--TEST--
cubrid_fetch_array
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--XFAIL--
ODBC driver returns garbage bytes for NUMERIC and BIT values.
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn,"drop table if exists fetch_arrary_tb");
odbc_exec($conn,"CREATE TABLE fetch_arrary_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
odbc_exec($conn,"insert into fetch_arrary_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
odbc_exec($conn,"insert into fetch_arrary_tb(c1,c2,c3,c4) values('string2222','char22222',2,11.11)");
odbc_exec($conn,"insert into fetch_arrary_tb(c5,c6,c7,c8,c9) values(TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31/2013',B'1',513254.3143513)");
odbc_exec($conn,"insert into fetch_arrary_tb(c10,c11) values(CHAR_TO_CLOB('This is a Dog2'), BIT_TO_BLOB(X'000010'))");


print("#####positive example#####\n");
$req1 = odbc_exec($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
if (!$req1) {
    printf("req1 [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
   $row1 = odbc_fetch_array($req1);
   var_dump($row1);
   
}
odbc_free_result($req1);

$req2=odbc_exec($conn,"select c1,c2,c3,c4,c5 from fetch_arrary_tb where c3=2");
if (!$req2) {
    printf("req2 [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
   $row2 = odbc_fetch_array($req2);
   printf("%s,%s,%d,%f,%s\n",array_values($row2)[0],array_values($row2)[1],array_values($row2)[2],array_values($row2)[3],array_values($row2)[4]);
   odbc_free_result($req2);
}

$req3=odbc_exec($conn, "select c5,c6,c7,c8,c9 from fetch_arrary_tb where c9 = 513254.3144");
if (!$req3) {
    printf("req3 [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
   $row3 = odbc_fetch_array($req3);
   printf("%s,%s,%s,%s,%f\n",$row3["c5"],$row3["c6"],$row3["c7"],$row3["c8"],$row3["c9"]);
   odbc_free_result($req3);
}

$req4= odbc_exec($conn, "select CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb order by c1 ");
while($row4 = odbc_fetch_object($req4)){
   var_dump($row4);
}
odbc_free_result($req4);

print("\n\n#####negative example#####\n");
$sql3="select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb where c3>10;";
$req5=odbc_exec($conn,$sql3);
if(false==$req5){
   printf("[001]execute [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   $row5 = odbc_fetch_array($req5);
   if(false==$row5){
      printf("[001]fetch_array [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
   }else{
      print("[001] fetch_array success\n");
      var_dump($row5);
   }
}
odbc_free_result($req5);

$req6=odbc_exec($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
try {
   $row6 = odbc_fetch_array($req6, 'CUBRID_NUMM');
} catch (Throwable $e) {
   $row6 = false;
}
if(empty($row6)){
      printf("[002] [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
   }else{
      print("[002] fetch success\n");
      var_dump($row6);
}
odbc_free_result($req6);

$req7=odbc_exec($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
try {
   $row7 = odbc_fetch_array($req7, 'CUBRID_ASSOCC');
} catch (Throwable $e) {
   $row7 = false;
}
if(empty($row7)){
      printf("[003] [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
   }else{
      print("[003] fetch_array success\n");
      var_dump($row7);
}
odbc_free_result($req7);

$req8=odbc_exec($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
try {
   $row8 = odbc_fetch_array($req8, 'CUBRID_OBJECTT');
} catch (Throwable $e) {
   $row8 = false;
}
if(empty($row8)){
      printf("[004] [%s] %s\n", odbc_error($conn), odbc_errormsg($conn));
   }else{
      print("[004] fetch_array success\n");
      var_dump($row8);
}
odbc_free_result($req8);

odbc_close($conn);

print "Finished!";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(22) {
  [0]=>
  string(12) "string111111"
  ["c1"]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  ["c2"]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
  ["c3"]=>
  string(1) "1"
  [3]=>
  string(19) "11.1099999999999994"
  ["c4"]=>
  string(19) "11.1099999999999994"
  [4]=>
  string(8) "02:10:00"
  ["c5"]=>
  string(8) "02:10:00"
  [5]=>
  string(10) "1977-08-14"
  ["c6"]=>
  string(10) "1977-08-14"
  [6]=>
  string(19) "1977-08-14 17:35:00"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  [7]=>
  string(2) "80"
  ["c8"]=>
  string(2) "80"
  [8]=>
  string(11) "432341.4321"
  ["c9"]=>
  string(11) "432341.4321"
  [9]=>
  string(13) "This is a Dog"
  ["clob_to_char(c10)"]=>
  string(13) "This is a Dog"
  [10]=>
  string(6) "000001"
  ["blob_to_bit(c11)"]=>
  string(6) "000001"
}
string2222,char22222           ,2,11.110000,
00:00:00,2008-10-31,2013-10-31 00:00:00,80,513254.314400
object(stdClass)#1 (2) {
  ["clob_to_char(c10)"]=>
  NULL
  ["blob_to_bit(c11)"]=>
  NULL
}
object(stdClass)#2 (2) {
  ["clob_to_char(c10)"]=>
  string(14) "This is a Dog2"
  ["blob_to_bit(c11)"]=>
  string(6) "000010"
}
object(stdClass)#1 (2) {
  ["clob_to_char(c10)"]=>
  string(13) "This is a Dog"
  ["blob_to_bit(c11)"]=>
  string(6) "000001"
}
object(stdClass)#2 (2) {
  ["clob_to_char(c10)"]=>
  NULL
  ["blob_to_bit(c11)"]=>
  NULL
}


#####negative example#####
[001]fetch_array %a 
[002] fetch success
%a
[003] fetch_array success
%a
[004] fetch_array success
%a
Finished!
