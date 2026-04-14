--TEST--
cubrid_autocommit cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
odbc_exec($conn, "DROP TABLE if exists commit1_tb");
odbc_exec($conn, 'CREATE TABLE commit1_tb(a int, b varchar(10))');
odbc_commit($conn);

printf("#####correct example#####\n");
//insert 
odbc_exec($conn, 'INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb');
$num_before = odbc_num_rows($req);
printf("Before rollback, record num: %d\n",$num_before);

odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb');
$num_after = odbc_num_rows($req);
printf("After rollback, record num: %d\n",$num_after);

//update
odbc_exec($conn, 'INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
odbc_commit($conn);
odbc_exec($conn, "update commit1_tb set b='hasname' where a=3 ");
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);

//
odbc_exec($conn, "delete from commit1_tb where a=3 ");
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);

//drop table
odbc_exec($conn, "drop table commit1_tb ");
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb');
if(FALSE == $req){
   printf("[001]Expect false, [%s] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   $result = odbc_fetch_array($req);
   printf("Before rollback:\n");
   var_dump($result);
}
odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);


printf("\n\n#####set autocommit true#####\n");
cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);
odbc_exec($conn, "INSERT INTO commit1_tb(a,b) values(8,'name8')");
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);


odbc_exec($conn, "delete from commit1_tb where a=8 ");
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = odbc_exec($conn, 'SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);



odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
#####correct example#####
Before rollback, record num: 3
After rollback, record num: 0
Before rollback:
array(2) {
  ["a"]=>
  string(1) "3"
  ["b"]=>
  string(7) "hasname"
}
After rollback:
array(2) {
  ["a"]=>
  string(1) "3"
  ["b"]=>
  NULL
}
Before rollback:
bool(false)
After rollback:
array(2) {
  ["a"]=>
  string(1) "3"
  ["b"]=>
  NULL
}

Warning: odbc_exec(): SQL error: %s
[001]Expect false, [%s] [%s]
After rollback:
array(2) {
  ["a"]=>
  string(1) "1"
  ["b"]=>
  NULL
}


#####set autocommit true#####
Before rollback:
array(2) {
  ["a"]=>
  string(1) "8"
  ["b"]=>
  string(5) "name8"
}
After rollback:
array(2) {
  ["a"]=>
  string(1) "8"
  ["b"]=>
  string(5) "name8"
}
Before rollback:
bool(false)
After rollback:
bool(false)
Finished!
