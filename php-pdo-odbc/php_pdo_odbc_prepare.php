<?php
$dsn = 'odbc:Driver={CUBRID Driver};db_name=demodb;server=192.168.2.32;port=33000';
$username = 'dba';
$password = '';
$insert_count = 100;

try {
    $options = getopt("c:");
    if (isset($options['c'])) {
        $insert_count = $options['c'];
    }
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Connected successfully");

    $pdo->exec('DROP TABLE IF EXISTS test_table');
    $createTableSQL = "CREATE TABLE test_table (id INT, name VARCHAR(255))";
    $pdo->exec($createTableSQL);
    error_log("Table created successfully");

    $startTime = microtime(true);

    $insertSQL = "INSERT INTO test_table (id, name) VALUES (:id, :name)";
    $pdo->beginTransaction();
    $stmt = $pdo->prepare($insertSQL);

    for ($i = 1; $i <= $insert_count; $i++) {
        $id = $i;
        $name = 'pdoodbc' . $i;
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
    }
    $pdo->commit();

    $endTime = microtime(true);
    $elapsed_time = $endTime - $startTime;
    error_log ("data inserted (elapsed time: " . $elapsed_time . "s)");

    $countSQL = "SELECT COUNT(*) as total FROM test_table";
    $stmt = $pdo->prepare($countSQL);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $rowCount = $row['total'];
    error_log("Data count after insert: " . $rowCount); 

    $selectSQL = "SELECT * FROM test_table";
    $stmt = $pdo->query($selectSQL);
    $rowCount = $stmt->rowCount();
    error_log("rowCount: " . $rowCount);

    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
        $rowCount++;
        //error_log("Row: " . $row['id'] . " - " . $row['name']);
    }

    $startTime = microtime(true);
    $selectSQL = "SELECT * FROM test_table where id = ?";
    $stmt = $pdo->prepare($selectSQL);


    $rowCount = 0;
    for ($i = 1; $i <= $insert_count; $i++) { 
        $stmt->execute([$i]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $rowCount++;
    }

    $endTime = microtime(true);
    $elapsed_time = $endTime - $startTime;
    error_log("data selected. rowCount : " . $rowCount . " elapsed_time: " . $elapsed_time . "s)");

} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
}
?>
