--TEST--
cubrid_connect
--XFAIL--
odbc_columnprivileges is not supported by CUBRID ODBC driver yet
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once('connect.inc');
printf("#####positive example#####\n");
$conn1 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn1) {
    printf("[001] [%s] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}else{
    printf("[001] user is dba\n");
    $res1= odbc_columnprivileges($conn1, null, "", "db_auth", "auth_type");
    $schema1 = [];
    if ($res1) {
        while ($row = odbc_fetch_array($res1)) {
            $schema1[] = $row;
        }
    }
    var_dump($schema1);
}
printf("\n");

$conn2= odbc_connect($cubrid_odbc_dsn, "public", "");
$res2 = odbc_columnprivileges($conn2, null, "", "db_auth", "auth_type");
$schema2 = [];
if ($res2) {
    while ($row = odbc_fetch_array($res2)) {
        $schema2[] = $row;
    }
}
printf("[002] user is public\n");
var_dump($schema2);

$conn3 = odbc_connect($cubrid_odbc_dsn, $user, "");
if (FALSE == $conn3) {
    printf("[003]No Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
    exit(3);
}elseif(TRUE == $conn3){
    printf("[003]Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[003]no true and no false\n");
}

odbc_close($conn1);
odbc_close($conn2);
odbc_close($conn3);

printf("\n\n#####negative example#####\n");
// Remove uid and pwd to ensure driver does not bypass our invalid credentials
$dsn_clean = preg_replace('/uid=[^;]*;pwd=[^;]*;?/i', '', $cubrid_odbc_dsn);

$conn4 = odbc_connect($dsn_clean, $user, "124456");
if (FALSE == $conn4) {
    printf("[004]Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn4){
    printf("[004]No Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[004]no true and no false\n");
}

$conn5 = odbc_connect($dsn_clean, 'dbaa', $passwd);
if (FALSE == $conn5) {
    printf("[005]Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn5){
    printf("[005]No Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[005]no true and no false\n");
}

$dsn6 = str_replace("DB_NAME=$db", "DB_NAME=nothisdb", $dsn_clean);
$conn6 = odbc_connect($dsn6, "public", "");
if (FALSE == $conn6) {
    printf("[006]Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn6){
    printf("[006]No Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[006]no true and no false\n");
}

$dsn7 = str_replace("DB_NAME=$db", "DB_NAME=demodb", $dsn_clean);
$conn7 = odbc_connect($dsn7, "public", "");
if (FALSE == $conn7) {
    printf("[007]No Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn7){
    printf("[007]Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[007]no true and no false\n");
}


$dsn8 = str_replace("DB_NAME=$db", "DB_NAME=" . "demodb", $dsn_clean);
$conn8 = odbc_connect($dsn8, "public", "");
if (FALSE == $conn8) {
    printf("[008]Expect: return value false, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}elseif(TRUE == $conn8){
    printf("[008]No Expect: return value true, [%s] [%s]\n", odbc_error(), odbc_errormsg());
}else{
    printf("[008]no true and no false\n");
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
[001] user is dba
array(0) {
}

Warning: odbc_columnprivileges(): SQL error: [unixODBC][Driver Manager]Driver does not support this function, SQL state IM001 in SQLColumnPrivileges in %s on line %d
[002] user is public
array(0) {
}
[003]Expect: return value true, [IM001] [[unixODBC][Driver Manager]Driver does not support this function]


#####negative example#####

Warning: odbc_connect(): SQL error: [CUBRID][ODBC CUBRID Driver][-171]Incorrect or missing password.%s, SQL state S1000 in SQLConnect in %s on line %d
[004]Expect: return value false, [S1000] [[CUBRID][ODBC CUBRID Driver][-171]Incorrect or missing password.%s]

Warning: odbc_connect(): SQL error: [CUBRID][ODBC CUBRID Driver][-165]User "dbaa" is invalid.%s, SQL state S1000 in SQLConnect in %s on line %d
[005]Expect: return value false, [S1000] [[CUBRID][ODBC CUBRID Driver][-165]User "dbaa" is invalid.%s]

Warning: odbc_connect(): SQL error: [CUBRID][ODBC CUBRID Driver][-677]Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s, SQL state S1000 in SQLConnect in %s on line %d
[006]Expect: return value false, [S1000] [[CUBRID][ODBC CUBRID Driver][-677]Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s]
[007]Expect: return value true, [S1000] [[CUBRID][ODBC CUBRID Driver][-677]Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s]
[008]No Expect: return value true, [S1000] [[CUBRID][ODBC CUBRID Driver][-677]Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s]
Finished!
