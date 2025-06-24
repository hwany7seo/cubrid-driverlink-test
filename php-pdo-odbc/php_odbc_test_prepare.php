<?php
ob_implicit_flush(true);
ini_set('output_buffering', 'off');
ini_set('implicit_flush', true);

$insert_count = 100;
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
    
    $insertSQL = "INSERT INTO test_table (id, name) VALUES (?, ?)";
    $stmt = odbc_prepare($conn, $insertSQL);
    for ($i = 1; $i <= $insert_count; $i++) {
        $name = 'phpodbc' . $i;
        odbc_execute($stmt, array($i, $name));
    }
    odbc_commit($conn);
    odbc_free_result($stmt);
    $endTime = microtime(true);
    $insertTime = $endTime - $startTime;
    error_log("Data inserted (elapsed time: " . $insertTime . "s)");

    
    $selectSQL = "SELECT * FROM test_table";
    $result = odbc_exec($conn, $selectSQL);
    if (!$result) {
        die("Failed to execute select: " . odbc_errormsg($conn));
    }
    
    error_log("Data selected successfully");
    
    $rows = array();
    $rowCount = 0;
    while ($row = odbc_fetch_array($result)) {
        $rows[] = $row;
        $rowCount++;
        if ($rowCount <= 5) {
            // 배열 구조 확인을 위한 디버깅
            error_log("Row structure: " . print_r($row, true));
            // id와 name 값 출력
            if (isset($row['id']) && isset($row['name'])) {
                error_log("Row {$rowCount}: ID={$row['id']}, Name={$row['name']}");
            } else {
                // 숫자 인덱스로 접근 시도
                error_log("Row {$rowCount}: ID={$row[0]}, Name={$row[1]}");
            }
        }
    }
    error_log("Total rows fetched: " . $rowCount);

    $startTime = microtime(true);
    $performanceCount = 0;
    for ($i = 1; $i <= $insert_count; $i++) {
        $selectSQL = "SELECT * FROM test_table WHERE id = $i";
        $result = odbc_exec($conn, $selectSQL);
        if (!$result) {
            die("Failed to execute select: " . odbc_errormsg($conn));
        }
        while ($row = odbc_fetch_array($result)) {
            $performanceCount++;
            if ($performanceCount <= 5) {
                // 성능 테스트에서도 몇 개 샘플 출력
                if (isset($row['id']) && isset($row['name'])) {
                    error_log("Performance test row: ID={$row['id']}, Name={$row['name']}");
                } else {
                    error_log("Performance test row: ID={$row[0]}, Name={$row[1]}");
                }
            }
        }
        odbc_free_result($result);
    }
    $endTime = microtime(true);
    $selectTime = $endTime - $startTime;
    error_log("Data selected (elapsed time: " . $selectTime . "s)");
    error_log("Performance test total rows: " . $performanceCount);

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
