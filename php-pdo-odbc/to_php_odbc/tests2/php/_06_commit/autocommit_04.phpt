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
try {
   $get5 = cubrid_get_autocommit($conn, '');
} catch (Throwable $e) {
   $get5 = false;
}
if(FALSE == $get5){
   printf("[005]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $get6 = cubrid_get_autocommit(null);
} catch (Throwable $e) {
   $get6 = false;
}
if(FALSE == $get6){
   printf("[006]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $get7 = cubrid_get_autocommit();
} catch (Throwable $e) {
   $get7 = false;
}
if(FALSE == $get7){
   printf("[007]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $set8 = cubrid_set_autocommit($conn);
} catch (Throwable $e) {
   $set8 = false;
}
if(FALSE == $set8){
   printf("[008]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}

try {
   $set9 = cubrid_set_autocommit();
} catch (Throwable $e) {
   $set9 = false;
}
if(FALSE == $set9){
   printf("[009]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
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
[006]Expect false, [] []
[007]Expect false, [] []
[008]Expect false, [] []
[009]Expect false, [] []
Finished!