<?php
ob_implicit_flush(true);
ini_set('output_buffering', 'off');
ini_set('implicit_flush', true);

$insert_count = 10;
// $dsn = "CUBRID Driver";
$dsn = "CUBRID Driver Unicode";
$user = "dba";
$password = "";

try {
    $conn = odbc_connect($dsn, $user, $password, SQL_CUR_USE_ODBC);
    if (!$conn) {
        die("Connection Failed: " . odbc_errormsg());
    }
    error_log("Connected successfully");

    odbc_exec($conn, 'DROP TABLE IF EXISTS test_table');
    error_log("Table drop successfully");
    
    odbc_exec($conn, 'CREATE TABLE test_table (id INT, name VARCHAR(255))');
    error_log("Table created successfully");

    $startTime = microtime(true);
    
    // 단일 INSERT 문으로 변경
    $values = array();
    for ($i = 1; $i <= $insert_count; $i++) {
        $name = 'odbcliba' . $i;
        $values[] = "($i, '$name')";
    }
    $insertSQL = "INSERT INTO test_table (id, name) VALUES " . implode(',', $values);
    
    if (!odbc_exec($conn, $insertSQL)) {
        die("Failed to execute insert: " . odbc_errormsg($conn));
    }
    
    $endTime = microtime(true);
    $insertTime = $endTime - $startTime;
    error_log("Data inserted (elapsed time: " . $insertTime . "s)");

    $startTime = microtime(true);
    $selectSQL = "SELECT * FROM test_table";
    readline("Press Enter to continue...");
    $result = odbc_exec($conn, $selectSQL);
    error_log("execute query");
    if (!$result) {
        die("Failed to execute select: " . odbc_errormsg($conn));
    }
    
    // $rowCount = odbc_num_rows($result);
    // $endTime = microtime(true);
    // $selectTime = $endTime - $startTime;
    // error_log("data selected. rowCount : " . $rowCount . " elapsed_time: " . $selectTime . "s)");

    // if ($rowCount == 0) {
    //     error_log("Warning: No rows were returned from the query");
    // }

    // for ($i = 0; $i < $rowCount; $i++) {
    //     $row = odbc_fetch_row($result, $i);
    //     error_log("row data is " . print_r($row, true));
    //     error_log("Row: " . $row['id'] . " " . $row['name']);
    // }
    $rowCount = 0;
    echo "Fetching results...\n";

    $rows = array();
    while ($row = odbc_fetch_array($result)) {
        $rows[] = $row;
        $rowCount++;
        if ($rowCount <= 5) {
            error_log("Row: " . $row['id'] . " " . $row['name']);
        }
    }

    odbc_free_result($result);
    odbc_close($conn);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    if (isset($conn)) {
        odbc_close($conn);
    }
} finally {
    error_log("End");
}
?>
