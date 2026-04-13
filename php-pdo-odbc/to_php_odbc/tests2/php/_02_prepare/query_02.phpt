--TEST--
cubrid_query cubrid_fress_result 
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
odbc_exec($conn, 'DROP TABLE IF EXISTS query_tb');
odbc_exec($conn,"CREATE TABLE query_tb(id int primary key, first_name varchar(10) default 'name', last_name varchar(20),comment string SHARED 'COMMENT')");
odbc_exec($conn,"insert into query_tb(id,first_name,last_name) values(1,'name1','last1'),(2,'name2','last2'),(3,'name3','last3')");

printf("#####negative example#####\n");
if (FALSE == ($tmp=null)) {
    printf("[001] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

if (FALSE == ($tmp = null)) {
    printf("[002] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

if (false == ($tmp=odbc_exec($conn, 'THIS IS NOT SQL'))) {
    printf("[003] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$unbuff=odbc_exec($conn, "select * from query_tb where id >10");
if (false == $unbuff) {
    printf("[004] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    $row=odbc_fetch_array($unbuff);
    var_dump($row);
}


printf("#####example for odbc_free_result()#####\n");
if(FALSE == odbc_free_result($unbuff)){
   printf("[005] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[005] Cubrid_free_result success\n");
}

$query2=odbc_exec($conn, "select * from query_tb where id >=3");
while ($row = odbc_fetch_array($query2)) {
   var_dump($row);
}
if(FALSE == odbc_free_result($query2)){
   printf("[006]No expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[006] Cubrid_free_result success\n");
}

if(FALSE == odbc_free_result($query2)){
   printf("[007] Expecting false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   printf("[007] Cubrid_free_result success\n");
}



odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####
[001] Expecting false, [0] []

[002] Expecting false, [0] []

Warning: Error: DBMS, -493, Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' %s in %s on line %d
[003] Expecting false, [-493] [Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' %s]
bool(false)
#####example for odbc_free_result()#####
[005] Cubrid_free_result success
array(4) {
  ["id"]=>
  string(1) "3"
  ["first_name"]=>
  string(5) "name3"
  ["last_name"]=>
  string(5) "last3"
  ["comment"]=>
  string(7) "COMMENT"
}
[006] Cubrid_free_result success
[007] Cubrid_free_result success
Finished!

