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
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
odbc_exec($conn, "DROP TABLE if exists commit1_tb");
cubrid_query('CREATE TABLE commit1_tb(a int, b varchar(10))');
cubrid_query('INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
odbc_commit($conn);

printf("#####negative example for odbc_commit()#####\n");
cubrid_query('INSERT INTO commit1_tb(a) VALUE(4)');
$conn_res=odbc_commit($conn,'');
if(FALSE == $conn_res){
   printf("[001]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$conn_res2=odbc_commit('');
if(FALSE == $conn_res2){
   printf("[002]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$conn_res3=odbc_commit();
if(FALSE == $conn_res3){
   printf("[003]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}
odbc_close($conn);

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
$req4 = cubrid_query('SELECT * FROM commit1_tb where a=4');
if(FALSE == $req4){
   printf("[004]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
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
cubrid_query("delete from commit1_tb where a=3 ");
$roll_res5=odbc_rollback($conn,'');
if(FALSE == $roll_res5){
   printf("[005]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$roll_res6=odbc_rollback('');
if(FALSE == $roll_res6){
   printf("[006]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$roll_res7=odbc_rollback();
if(FALSE == $roll_res7){
   printf("[007]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

odbc_commit($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
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

Warning: odbc_commit() expects exactly 1 parameter, 2 given in %s on line %d
[001]Expect false, [0] []

Warning: odbc_commit() expects parameter 1 to be resource, string given in %s on line %d
[002]Expect false, [0] []

Warning: odbc_commit() expects exactly 1 parameter, 0 given in %s on line %d
[003]Expect false, [0] []
[004]
bool(false)
Expect: autocommit is OFF.

#####negative example for odbc_rollback()#####

Warning: odbc_rollback() expects exactly 1 parameter, 2 given in %s on line %d
[005]Expect false, [0] []

Warning: odbc_rollback() expects parameter 1 to be resource, string given in %s on line %d
[006]Expect false, [0] []

Warning: odbc_rollback() expects exactly 1 parameter, 0 given in %s on line %d
[007]Expect false, [0] []
Rollback failed result:
bool(false)
Finished!
