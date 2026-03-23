--TEST--
cubrid_drop and table contains partiton
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
printf("negative testing\n");

include_once("connect.inc");
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
odbc_exec($conn, "drop table if exists partition_tb");
odbc_exec($conn, "create table partition_tb(id int ,test_char char(50),test_varchar varchar(2000))");
$alterSql="ALTER TABLE partition_tb PARTITION BY LIST (test_char) (PARTITION p0 VALUES IN ('aaa','bbb','ddd'),PARTITION p1 VALUES IN ('fff','ggg','hhh',NULL),PARTITION p2 VALUES IN ('kkk','lll','mmm') )";
$insertSql="insert into partition_tb values(1,'aaa','aaa')";
$insertSql2="insert into partition_tb values(5,'ggg','ggg')";
odbc_exec($conn,$alterSql);
odbc_exec($conn, $insertSql);
odbc_exec($conn,$insertSql2);

$req = odbc_exec($conn, "select * from partition_tb where id >10 ", CUBRID_INCLUDE_OID);

$oid = cubrid_current_oid($req);
if (FALSE==$oid){
    printf("Expect false for oid [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    printf("oid: %s\n",$oid);
}
$tmp=cubrid_drop($conn, $oid);
if (FALSE ==$tmp){
    printf("Expect false for cubrid_drop, [%d] [%s] \n",odbc_error(),odbc_errormsg());
}
else {
    printf("drop success\n");
}

$tmp2=cubrid_drop($conn,$nothisoid);
if (FALSE ==$tmp2){
    printf("[002]Expect false for cubrid_drop, [%d] [%s] \n",odbc_error(),odbc_errormsg());
}
else {
    printf("drop success\n");
}


odbc_free_result($req);


odbc_close($conn);

print "Fished!\n";
?>
--CLEAN--
--EXPECTF--
negative testing

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d
Expect false for oid [-10012] Invalid cursor position

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
Expect false for cubrid_drop, [-20020] [Invalid oid string] 

Notice: Undefined variable: nothisoid in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
[002]Expect false for cubrid_drop, [-20020] [Invalid oid string] 
Fished!
