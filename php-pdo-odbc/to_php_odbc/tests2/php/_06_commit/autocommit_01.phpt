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
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
odbc_exec($conn, "DROP TABLE if exists commit1_tb");
cubrid_query('CREATE TABLE commit1_tb(a int, b varchar(10))');
odbc_commit($conn);

printf("#####correct example#####\n");
//insert 
cubrid_query('INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
$req = cubrid_query('SELECT * FROM commit1_tb');
$num_before = odbc_num_rows($req);
printf("Before rollback, record num: %d\n",$num_before);

odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb');
$num_after = odbc_num_rows($req);
printf("After rollback, record num: %d\n",$num_after);

//update
cubrid_query('INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
odbc_commit($conn);
cubrid_query("update commit1_tb set b='hasname' where a=3 ");
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);

//
cubrid_query("delete from commit1_tb where a=3 ");
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);

//drop table
cubrid_query("drop table commit1_tb ");
$req = cubrid_query('SELECT * FROM commit1_tb');
if(FALSE == $req){
   printf("[001]Expect false, [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
}else{
   $result = odbc_fetch_array($req);
   printf("Before rollback:\n");
   var_dump($result);
}
odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);


printf("\n\n#####set autocommit true#####\n");
cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);
cubrid_query("INSERT INTO commit1_tb(a,b) values(8,'name8')");
$req = cubrid_query('SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("After rollback:\n");
var_dump($result);


cubrid_query("delete from commit1_tb where a=8 ");
$req = cubrid_query('SELECT * FROM commit1_tb where a=8');
$result = odbc_fetch_array($req);
printf("Before rollback:\n");
var_dump($result);

odbc_rollback($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=8');
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

Warning: Error: DBMS, -493, Syntax: Unknown class "dba.commit1_tb". select * from [dba.commit1_tb]%s in %s on line %d
[001]Expect false, [-493] [Syntax: Unknown class "dba.commit1_tb". select * from [dba.commit1_tb]%s]
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
