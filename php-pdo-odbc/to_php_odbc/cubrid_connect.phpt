--TEST--
cubrid_connect
--SKIPIF--
--FILE--
<?php
include_once("connect.inc");
$tmp = NULL;
$conn = NULL;
if (!is_null($tmp = @odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", ""))) {
    printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}
$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[002] [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}
$conn1 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if ($conn != $conn1) {
    printf("[003] The new_link parameter in cubrid_connect does not work!\n");
}
if ($conn == $conn2) {
    printf("[004] Can not make a new connection with the same parameters!");
}
odbc_close($conn);
odbc_close($conn2);
 
// invalid db
#$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if($conn2 == false){
     printf("[007] [%d] %s\n", odbc_error(), odbc_errormsg());
}
 
// invalid password
#$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if($conn2 == false){
     printf("[008] [%d] %s\n", odbc_error(), odbc_errormsg());
}
$conn2 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if($conn2 == false){
	 printf("[009] [%d] %s\n", odbc_error(), odbc_errormsg());
}
#$conn1 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
$conn1 = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if($conn1 == false){
     printf("[010] [%d] %s\n", odbc_error(), odbc_errormsg());
}
print "done!";
?>

<?php
$user = "public_error_user";
$passwd = "";
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[005] [%d] %s\n", odbc_error(), odbc_errormsg());
}
$user = "public";
$passwd = "wrong_password";
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[006] [%d] %s\n", odbc_error(), odbc_errormsg());
}
?>

--CLEAN--
--EXPECTF--
Warning: Error: DBMS, -%d, Failed to connect to database server, %s
[007] [-%d] Failed to connect to database server, %s

Warning: Error: DBMS, -171, Incorrect or missing password.%s
[008] [-171] Incorrect or missing password.%s

Warning: Error: CCI, -20016, Cannot connect to CUBRID CAS in %s
[009] [-20016] Cannot connect to CUBRID CAS

Warning: Error: DBMS, -165, User "invalid_user" is invalid.%s
[010] [-165] User "invalid_user" is invalid.%s
done!

Warning: Error: DBMS, -165, User "%s" is invalid.%s in %s on line %d
[005] [-165] User "%s" is invalid.%s

Warning: Error: DBMS, -171, Incorrect or missing password.%s in %s on line %d
[006] [-171] Incorrect or missing password.%s
