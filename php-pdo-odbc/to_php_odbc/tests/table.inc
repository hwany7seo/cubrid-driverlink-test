<?php
require_once 'connect.inc';
require_once 'until.php';

if (!$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "")) {
    printf("Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
    exit(1);
}

$retval = check_table_existence($conn, "php_cubrid_test");
if($retval == -1) {
    exit(1);
} elseif ($retval == 1) {
    cubrid_query('DROP TABLE php_cubrid_test', $conn);
}

$cubrid_req = cubrid_query("CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30), e blob, f clob)", $conn);
if (!$cubrid_req) {
    printf("Failed to create test table: [%d] %s\n", odbc_error(), odbc_errormsg());
    exit(1);
}

if (!odbc_commit($conn)) {
    exit(1);
}

?>
