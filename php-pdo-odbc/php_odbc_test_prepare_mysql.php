<?php
ob_implicit_flush(true);
ini_set('output_buffering', 'off');
ini_set('implicit_flush', true);

$insert_count = 10;
$dsn = "MYSQL ODBC UNICODE";
$user = "hwanyseo";
$password = "Cubrid123!@#";

try {
    $conn = odbc_connect($dsn, $user, $password, SQL_CUR_USE_ODBC);
    if (!$conn) {
        die("Connection Failed: " . odbc_errormsg());
    }
    echo "Connected successfully\n";

    odbc_exec($conn, 'DROP TABLE IF EXISTS test_table');
    echo "Table drop successfully\n";
    
    odbc_exec($conn, 'CREATE TABLE test_table (id INT, name VARCHAR(255))');
    echo "Table created successfully\n";

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
    echo "Data inserted (elapsed time: " . $insertTime . "s)\n";

    $startTime = microtime(true);
    
    // 결과를 한 번에 가져오는 방식으로 변경
    $selectSQL = "SELECT * FROM test_table";
    echo "selectSQL: " . $selectSQL . "\n";
    
    $result = odbc_exec($conn, $selectSQL);
    if (!$result) {
        die("Failed to execute select: " . odbc_errormsg($conn));
    }
    echo "Query executed successfully\n";

    $rowCount = 0;
    echo "Fetching results...\n";
    
    // 결과 세트의 컬럼 수 확인
    $numFields = odbc_num_fields($result);
    echo "Number of columns: " . $numFields . "\n";
    
    // 결과를 배열로 저장
    $rows = array();
    while ($row = odbc_fetch_array($result)) {
        $rows[] = $row;
        $rowCount++;
        if ($rowCount <= 5) {
            echo "Row " . $rowCount . ": id=" . $row['id'] . ", name=" . $row['name'] . "\n";
        }
    }
    
    $endTime = microtime(true);
    $selectTime = $endTime - $startTime;
    echo "Data selected. Total rows: " . $rowCount . ", elapsed_time: " . $selectTime . "s\n";

    if ($rowCount == 0) {
        echo "Warning: No rows were returned from the query\n";
    }

    odbc_free_result($result);
    odbc_close($conn);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        odbc_close($conn);
    }
} finally {
    echo "End\n";
}
?>
