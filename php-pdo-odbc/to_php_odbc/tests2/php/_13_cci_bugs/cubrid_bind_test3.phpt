--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only API — Not supported by PHP ODBC (non-standard bind)");
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, 'DROP TABLE IF EXISTS bit1_tb');
odbc_exec($conn, 'DROP TABLE IF EXISTS bit2_tb');
odbc_exec($conn, 'DROP TABLE IF EXISTS bit3_tb');
$sql = <<<EOD
CREATE TABLE bit1_tb(a1 bit(8));
EOD;
odbc_exec($conn,$sql);

odbc_exec($conn,"create table bit2_tb(a2 bit(8))");
odbc_exec($conn,"insert into bit2_tb values(B'1010'),(0xaa)");

printf("#####select from bit2_tb #####\n");
$req1=odbc_exec($conn,"select * from bit2_tb"); 
if ($req1) {
      $res=cubrid_fetch($req1);
      print_r($res);  
      odbc_free_result($req1); 
}

printf("\n\n#####select from bit1_tb #####\n");
$req2 = odbc_prepare($conn, 'INSERT INTO bit1_tb VALUES(?)');
if(!$tmp=cubrid_bind($req2, 1,"B'1010'",'bit')){
   printf("[%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
cubrid_execute($req2);
$req3 = odbc_exec($conn, "SELECT * FROM bit1_tb");
$result = odbc_fetch_array($req3);
var_dump($result);
odbc_free_result($req3);

printf("\n\n#####select from bit3_tb #####\n");
odbc_exec($conn,"create table bit3_tb(a3 bit(8))");
$req4 = odbc_prepare($conn, 'INSERT INTO bit3_tb VALUES(?)');
if(!$tmp=cubrid_bind($req4, 1,B'10100000','bit')){
   printf("[%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}
cubrid_execute($req4);
$req5 = odbc_exec($conn, "SELECT * FROM bit3_tb");
$result5 = odbc_fetch_array($req5);
var_dump($result5);
odbc_free_result($req5);

odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####select from bit2_tb #####
Array
(
    [0] => A0
    [a2] => A0
)


#####select from bit1_tb #####

Warning: Error: CCI, -20008, Type conversion error in %s on line %d
[-20008] Type conversion error

Warning: Error: CLIENT, -30015, Some parameter not binded in %s on line %d
bool(false)


#####select from bit3_tb #####
array(1) {
  ["a3"]=>
  string(2) "A0"
}
Finished!
