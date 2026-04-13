<?php
require_once 'connect.inc';
require_once 'until.php';

if (!$conn = cubrid_connect($host, $port, $db,  $user, $passwd)) {
    printf("Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
    exit(1);
}

$retval = check_table_existence($conn, "php_cubrid_test");
if($retval == -1) {
    exit(1);
} elseif ($retval == 1) {
    odbc_exec($conn, 'DROP TABLE php_cubrid_test');
}

$cubrid_req = odbc_exec($conn, "CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30), e blob, f clob)");
if (!$cubrid_req) {
    printf("Failed to create test table: [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}

if (!cubrid_commit($conn)) {
    exit(1);
}

?>
