--TEST--
cubrid_num_rows 
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
//Row of selecting result is one
$delete_result1=odbc_exec($conn, "drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result1=odbc_exec($conn, "create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . odbc_errormsg());
}

$sql_statment1="insert into numeric_tb values(-32768,32767,2147483647,-9223372036854775808,0.12345678,12345.6789,-3.402823466E+38,+3.402823466E+38,-3.402823466E+38,-1.7976931348623157E+308);";
$sql_statement2="SELECT * FROM numeric_tb;";
$insert_result=odbc_exec($conn, $sql_statment1);

$res = odbc_exec($conn, $sql_statement2);

$row_num = odbc_num_rows($res);
if ($row_num < 0) {
	printf("[001] Unexpected odbc_num_rows: %d [%s] %s\n", $row_num, odbc_error($conn), odbc_errormsg($conn));
}
$col_num = odbc_num_fields($res);
if ($col_num < 0) {
	printf("[001b] Unexpected odbc_num_fields: %d\n", $col_num);
}
$field_num = odbc_num_fields($res);
if ($col_num < 0) {
	printf("[001c] Unexpected odbc_num_fields: %d\n", $col_num);
}
printf("Values of column: %d\n",$field_num);


//Row of selecting result is null
$delete_result=odbc_exec($conn, "drop class if exists date_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class date_tb(date_t date, time_t time, timestamp_t timestamp, datetime_t datetime)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}
$sql1="insert into date_tb values( DATE '08/14/1977', TIME '02:10:00', TIMESTAMP '08/14/1977 5:35:00 pm',DATETIME '10/31'),( DATE '08/14/1977', TIME '02:10:00', TIMESTAMP '08/15/1977 5:35:00 pm',DATETIME '13:15:45 10/31/2008'),( null, null, null,DATETIME '10/31/2008 01:15:45 PM')";
$sql2="select date_t,time_t,datetime_t from date_tb  where date_t = date_t order by 1,2,3";
$sql3="select date_t,time_t,datetime_t from date_tb  where date_t <> date_t order by 1,2,3";
$result1=odbc_exec($conn, $sql1);
$result2=odbc_exec($conn, $sql2);
$result3=odbc_exec($conn, $sql3);

$row_num = odbc_num_rows($result2);
$col_num = odbc_num_fields($result2);
$field_num = odbc_num_fields($result2);
printf("Values of column: %d\n",$field_num);

$row_num = odbc_num_rows($result3);
$col_num = odbc_num_fields($result3);
$field_num = odbc_num_fields($result3);
printf("Values of column: %d\n",$field_num);

odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
Values of column: 10
Values of column: 3
Values of column: 3
Finished!
