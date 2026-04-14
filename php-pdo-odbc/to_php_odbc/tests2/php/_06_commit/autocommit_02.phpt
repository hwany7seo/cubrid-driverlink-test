--TEST--
cubrid_commit cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
odbc_exec($conn, "DROP TABLE if exists commit1_tb");
odbc_exec($conn, 'CREATE TABLE commit1_tb(a int, b varchar(10))');
odbc_exec($conn, 'INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
odbc_commit($conn);

printf("#####negative example for odbc_commit()#####\n");
odbc_exec($conn, 'INSERT INTO commit1_tb(a) VALUE(4)');
try {
   $conn_res = odbc_commit($conn, '');
} catch (Throwable $e) {
   $conn_res = false;
}
if(FALSE == $conn_res){
   printf("[001]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $conn_res2 = odbc_commit('');
} catch (Throwable $e) {
   $conn_res2 = false;
}
if(FALSE == $conn_res2){
   printf("[002]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $conn_res3 = odbc_commit();
} catch (Throwable $e) {
   $conn_res3 = false;
}
if(FALSE == $conn_res3){
   printf("[003]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}
odbc_close($conn);

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
$req4 = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=4');
if(FALSE == $req4){
   printf("[004]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   $result = odbc_fetch_array($req4);
   printf("[004]\n");
   var_dump($result);
}


cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
if (cubrid_get_autocommit($conn)) {
    printf("No expect :autocommit is ON.\n");
} else {
    printf("Expect: autocommit is OFF.");
}

printf("\n\n#####negative example for odbc_rollback()#####\n");
odbc_exec($conn, "delete from commit1_tb where a=3 ");
try {
   $roll_res5 = odbc_rollback($conn, '');
} catch (Throwable $e) {
   $roll_res5 = false;
}
if(FALSE == $roll_res5){
   printf("[005]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $roll_res6 = odbc_rollback('');
} catch (Throwable $e) {
   $roll_res6 = false;
}
if(FALSE == $roll_res6){
   printf("[006]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $roll_res7 = odbc_rollback();
} catch (Throwable $e) {
   $roll_res7 = false;
}
if(FALSE == $roll_res7){
   printf("[007]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

odbc_commit($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("Rollback failed result:\n");
var_dump($result);

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
#####negative example for odbc_commit()#####
[001]Expect false, [] []
[002]Expect false, [] []
[003]Expect false, [] []
[004]
bool(false)
Expect: autocommit is OFF.

#####negative example for odbc_rollback()#####
[005]Expect false, [] []
[006]Expect false, [] []
[007]Expect false, [] []
Rollback failed result:
bool(false)
Finished!