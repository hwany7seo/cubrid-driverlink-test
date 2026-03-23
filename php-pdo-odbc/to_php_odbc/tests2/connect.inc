<?php
$host = "test-db-server";
$port = 33000;
$db = "phpdb";
$user = "dba";
$passwd = "";
$connect_url = "CUBRID:$host:$port:$db:::";
$cubrid_odbc_dsn = "Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db;
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
?>
