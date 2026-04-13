--TEST--
cubrid_get_autocommit cubrid_set_autocommit
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

printf("#####correct example#####\n");
if (cubrid_get_autocommit($conn)) {
    printf("[001]Expect: autocommit is ON.\n");
} else {
    printf("[001]No expect: autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
if (cubrid_get_autocommit($conn)) {
    printf("[002]No expect: autocommit is ON.\n");
} else {
    printf("[002]Expect: autocommit is OFF.\n");
}
odbc_commit($conn);
odbc_close($conn);

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("[003]Expect: autocommit is ON.\n");
} else {
    printf("[003]No Expect: autocommit is OFF.\n");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);
odbc_close($conn);

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("[004]Expect: autocommit is ON.\n");
} else {
    printf("[004]No expect: autocommit is OFF.");
}


printf("\n\n#####negative example#####\n");
$get5=cubrid_get_autocommit($conn,'');
if(FALSE == $get5){
   printf("[005]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$get6=cubrid_get_autocommit(null);
if(FALSE == $get6){
   printf("[006]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$get7=cubrid_get_autocommit();
if(FALSE == $get7){
   printf("[007]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$set8=cubrid_set_autocommit($conn);
if(FALSE == $set8){
   printf("[008]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

$set9=cubrid_set_autocommit();
if(FALSE == $set9){
   printf("[009]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}


odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct example#####
[001]Expect: autocommit is ON.
[002]Expect: autocommit is OFF.
[003]Expect: autocommit is ON.
[004]Expect: autocommit is ON.


#####negative example#####

Warning: cubrid_get_autocommit() expects exactly 1 parameter, 2 given in %s on line %d
[005]Expect false, [0] []

Warning: cubrid_get_autocommit() expects parameter 1 to be resource, null given in %s on line %d
[006]Expect false, [0] []

Warning: cubrid_get_autocommit() expects exactly 1 parameter, 0 given in %s on line %d
[007]Expect false, [0] []

Warning: cubrid_set_autocommit() expects exactly 2 parameters, 1 given in %s on line %d
[008]Expect false, [0] []

Warning: cubrid_set_autocommit() expects exactly 2 parameters, 0 given in %s on line %d
[009]Expect false, [0] []
Finished!
