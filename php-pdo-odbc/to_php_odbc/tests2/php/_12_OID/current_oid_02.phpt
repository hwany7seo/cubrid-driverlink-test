--TEST--
cubrid_current_oid and multiset type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");


//multiset and cubrid_current_oid
$delete_result=cubrid_query("drop class if exists multiset_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=cubrid_query("create table multiset_tb(id int primary key,
        sInteger multiset(integer,monetary),
	sFloat multiset(float,date,time),
	sDouble multiset(double)
)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$sql1="insert into multiset_tb values(1,
{11111,345,999.1111},
{234.43145,33444,DATE '08/14/1977', TIME '02:10:00'},
{4444.000,434000,114.343}
)";
$sql2="insert into multiset_tb values(2,
{1,3,4,5,23.2,43.4},
{null,null,DATE '08/14/1977', TIME '02:10:00'},
{13.00}
)";
odbc_exec($conn,$sql1);
$req = odbc_exec($conn, $sql2, CUBRID_INCLUDE_OID);
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[001] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}

$req = odbc_exec($conn, "select * from multiset_tb where id > 3 ", CUBRID_INCLUDE_OID);
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}


printf("\n\n");
odbc_free_result($req);
odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
Warning: Error: CLIENT, -30002, Invalid API call in %s on line %d

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d


Finished!
