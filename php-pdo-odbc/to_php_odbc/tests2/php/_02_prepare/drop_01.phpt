--TEST--
cubrid_drop and table contains partiton
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
printf("positive testing\n");

include_once("connect.inc");
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
@odbc_exec($conn, "drop table if exists partition_tb");
odbc_exec($conn, "create table partition_tb(id int not null,test_char char(50),test_varchar varchar(2000), test_bit bit(16),test_varbit bit varying(20),test_nchar nchar(50),test_nvarchar nchar varying(2001),test_string string,test_datetime timestamp, primary key(id, test_char)) DONT_REUSE_OID");
$alterSql="ALTER TABLE partition_tb PARTITION BY LIST (test_char) (PARTITION p0 VALUES IN ('aaa','bbb','ddd'),PARTITION p1 VALUES IN ('fff','ggg','hhh',NULL),PARTITION p2 VALUES IN ('kkk','lll','mmm') )";
$insertSql="insert into partition_tb values(1,'aaa','aaa',B'1',B'1011',N'aaa',N'aaa','aaaaaaaaaa','2006-03-01 09:00:00')";
$insertSql2="insert into partition_tb values(5,'ggg','ggg',B'101',B'1111',N'ggg',N'ggg','gggggggggg','2006-03-01 09:00:00')";
odbc_exec($conn,$alterSql);
odbc_exec($conn, $insertSql);
odbc_exec($conn,$insertSql2);

$req = odbc_exec($conn, "select * from partition_tb", CUBRID_INCLUDE_OID);
if (!$req) {
    printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    $oid = cubrid_current_oid($req);
    if (is_null ($oid)){
        printf("[003] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
    }else{
        printf("%d---oid: %s\n",__LINE__, $oid);
    }
    printf("Results before drop\n");
    while($row=odbc_fetch_row($req)){
       print_r($row);
    }
    if (cubrid_drop($conn, $oid)){
        odbc_commit($conn);
    }else {
        odbc_rollback($conn);
    }
}
odbc_free_result($req);


if (!$req = odbc_exec($conn, "select * from partition_tb", CUBRID_INCLUDE_OID)) {
    printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
}else{
    $oid = cubrid_current_oid($req);
    printf("The first record's oid: %s\n",$oid);
    printf("Results after drop\n");
    while($row=odbc_fetch_row($req)){
       print_r($row);
    }
    odbc_free_result($req);
}

odbc_close($conn);

print "Fished!\n";
?>
--CLEAN--
--EXPECTF--
positive testing
23---oid: %s
Results before drop
Array
(
    [0] => 1
    [1] => aaa                                               
    [2] => aaa
    [3] => 8000
    [4] => B0
    [5] => aaa                                               
    [6] => aaa
    [7] => aaaaaaaaaa
    [8] => 2006-03-01 09:00:00
)
Array
(
    [0] => 5
    [1] => ggg                                               
    [2] => ggg
    [3] => A000
    [4] => F0
    [5] => ggg                                               
    [6] => ggg
    [7] => gggggggggg
    [8] => 2006-03-01 09:00:00
)
The first record's oid: %s
Results after drop
Array
(
    [0] => 5
    [1] => ggg                                               
    [2] => ggg
    [3] => A000
    [4] => F0
    [5] => ggg                                               
    [6] => ggg
    [7] => gggggggggg
    [8] => 2006-03-01 09:00:00
)
Fished!
