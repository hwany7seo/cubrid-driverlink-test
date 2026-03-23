<?php
require_once 'connectLarge.inc';
require_once 'until.php';

if (!$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "")) {
    printf("Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
    exit(1);
}

$retval = check_table_existence($conn, "largetable");
if($retval == -1) {
    exit(1);
}elseif($retval == 1) {
    printf("this table is created\n");
}else{
    printf("#####start: create largetable#####\n");
    $cubrid_req = odbc_exec($conn, "CREATE TABLE largetable(a int AUTO_INCREMENT, b clob)");
    if (!$cubrid_req) {
        printf("Failed to create test table: [%d] %s\n", cubrid_error_code(), odbc_errormsg());
        exit(1);
    }
    
    $req = odbc_prepare($conn, "insert into largetable(b) values (?)");
    $importName=array("largeFile/large.txt");
    for($i=0; $i<count($importName); $i++){
        $lob=cubrid_lob2_new($conn, "CLOB");
        cubrid_lob2_import($lob, $importName[$i]);
        cubrid_lob2_bind($req, 1 , $lob, "CLOB");
        odbc_execute($req);
        cubrid_lob2_close($lob);
    }
    odbc_free_result($req);
    
    if (!odbc_commit($conn)) {
        exit(1);
    }
}

?>

