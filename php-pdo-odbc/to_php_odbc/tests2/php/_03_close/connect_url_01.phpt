--TEST--
cubrid_connect_with_url
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
printf("#####positive example#####\n");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", odbc_error(), odbc_errormsg());
}

$conn1 = odbc_connect($cubrid_odbc_dsn, "", "");
$conn2 = odbc_connect($cubrid_odbc_dsn, "", "");
if ($conn != $conn1) {
    printf("[002] The new_link parameter in cubrid_connect_with_url does not work!\n");
}
if ($conn == $conn2) {
    printf("[003] Can not make a new connection with the same parameters!");
}
$conn4 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn4) {
    printf("[004] [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[004]Connect success\n");
}

$conn5=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn5) {
    printf("[005]No expect: return value false. [%d] %s\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn5){
    printf("[005]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn5);
    printf("[005]autocommit value: %s\n",$autocommit);
}
printf("\n\n");
$conn6=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn6) {
    printf("[006]No expect: return value false. [%d] %s\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn6){
    printf("[006]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn6);
    printf("[006]autocommit value: %s\n",$autocommit);
    $db_params = cubrid_get_db_parameter($conn6);
    foreach ($db_params as $param_name => $param_value)  {
       printf("%-30s %s\n", $param_name, $param_value);
    }
}

odbc_close($conn);
odbc_close($conn1);
odbc_close($conn2);
odbc_close($conn4);
odbc_close($conn5);
odbc_close($conn6);

$conn7=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn7) {
    printf("[007]No Expect: return value false. [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn7){
    printf("[007]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn7);
    printf("[007]autocommit value: %s\n",$autocommit);
}
odbc_close($conn7);

printf("\n\n#####negative example for disconnect and close#####\n");
$conn8=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn8) {
    printf("[008]Expect: return value false. [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn8){
    printf("[008]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn8);
    printf("[008]autocommit value: %s\n",$autocommit);
}

$conn8=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn8) {
    printf("[009]Expect: return value false. [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn8){
    printf("[009]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn8);
    printf("[009]autocommit value: %s\n",$autocommit);
}

$conn9=odbc_connect($cubrid_odbc_dsn, "", "");
if (FALSE == $conn9) {
    printf("[010]Expect: return value false. [%d] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn9){
    printf("[010]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn9);
    printf("[010]autocommit value: %s\n",$autocommit);
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
[004]Connect success
[005]Expect: return value true
[005]autocommit value: 1


[006]Expect: return value true
[006]autocommit value: 1
PARAM_ISOLATION_LEVEL          4
PARAM_LOCK_TIMEOUT             -1
PARAM_MAX_STRING_LENGTH        1073741823
PARAM_AUTO_COMMIT              1

Warning: odbc_close(): supplied resource is not a valid CUBRID Connect resource in %s on line %d

Warning: odbc_close(): supplied resource is not a valid CUBRID Connect resource in %s on line %d

Warning: odbc_close(): supplied resource is not a valid CUBRID Connect resource in %s on line %d
[007]Expect: return value true
[007]autocommit value: 1


#####negative example for disconnect and close#####

Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[008]Expect: return value false. [-20030] [Invalid url string]

Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[009]Expect: return value false. [-20030] [Invalid url string]

Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[010]Expect: return value false. [-20030] [Invalid url string]
Finished!
