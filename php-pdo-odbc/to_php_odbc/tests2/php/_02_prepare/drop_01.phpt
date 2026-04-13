--TEST--
cubrid_drop and table contains partiton
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
printf("positive testing\n");

include_once('connect.inc');
$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed\n");
	exit(1);
}
@odbc_exec($conn, 'DROP TABLE IF EXISTS partition_tb');
odbc_exec($conn, "CREATE TABLE partition_tb(id int not null,test_char char(50),test_varchar varchar(2000), test_bit bit(16),test_varbit bit varying(20),test_nchar nchar(50),test_nvarchar nchar varying(2001),test_string string,test_datetime timestamp, primary key(id, test_char)) DONT_REUSE_OID");
$alterSql = "ALTER TABLE partition_tb PARTITION BY LIST (test_char) (PARTITION p0 VALUES IN ('aaa','bbb','ddd'),PARTITION p1 VALUES IN ('fff','ggg','hhh',NULL),PARTITION p2 VALUES IN ('kkk','lll','mmm') )";
$insertSql = "INSERT INTO partition_tb VALUES(1,'aaa','aaa',B'1',B'1011',N'aaa',N'aaa','aaaaaaaaaa','2006-03-01 09:00:00')";
$insertSql2 = "INSERT INTO partition_tb VALUES(5,'ggg','ggg',B'101',B'1111',N'ggg',N'ggg','gggggggggg','2006-03-01 09:00:00')";
odbc_exec($conn, $alterSql);
odbc_exec($conn, $insertSql);
odbc_exec($conn, $insertSql2);

printf("%d---sql-delete: first row (id=1, test_char='aaa')\n", __LINE__);

$req = odbc_exec($conn, 'SELECT * FROM partition_tb ORDER BY id');
if (!$req) {
	printf("[002] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("Results before drop\n");
	while (($row = odbc_fetch_array($req)) !== false) {
		$row['test_bit'] = strtoupper(bin2hex($row['test_bit']));
		$row['test_varbit'] = strtoupper(bin2hex($row['test_varbit']));
		print_r($row);
	}
	odbc_free_result($req);
}

if (!odbc_exec($conn, "DELETE FROM partition_tb WHERE id = 1 AND TRIM(test_char) = 'aaa'")) {
	printf("[del] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	odbc_commit($conn);
}

if (!$req = odbc_exec($conn, 'SELECT * FROM partition_tb ORDER BY id')) {
	printf("[004] [%d] %s\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("The first record's oid: n/a-odbc\n");
	printf("Results after drop\n");
	while (($row = odbc_fetch_array($req)) !== false) {
		$row['test_bit'] = strtoupper(bin2hex($row['test_bit']));
		$row['test_varbit'] = strtoupper(bin2hex($row['test_varbit']));
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
19---sql-delete: first row (id=1, test_char='aaa')
Results before drop
Array
(
    [id] => 1
    [test_char] => aaa                                               
    [test_varchar] => aaa
    [test_bit] => 8000
    [test_varbit] => B0
    [test_nchar] => aaa                                               
    [test_nvarchar] => aaa
    [test_string] => aaaaaaaaaa
    [test_datetime] => 2006-03-01 09:00:00
)
Array
(
    [id] => 5
    [test_char] => ggg                                               
    [test_varchar] => ggg
    [test_bit] => A000
    [test_varbit] => F0
    [test_nchar] => ggg                                               
    [test_nvarchar] => ggg
    [test_string] => gggggggggg
    [test_datetime] => 2006-03-01 09:00:00
)
The first record's oid: n/a-odbc
Results after drop
Array
(
    [id] => 5
    [test_char] => ggg                                               
    [test_varchar] => ggg
    [test_bit] => A000
    [test_varbit] => F0
    [test_nchar] => ggg                                               
    [test_nvarchar] => ggg
    [test_string] => gggggggggg
    [test_datetime] => 2006-03-01 09:00:00
)
Fished!