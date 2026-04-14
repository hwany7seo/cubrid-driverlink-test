--TEST--
odbc_fetch_array (ODBC: odbc_fetch_array + odbc_fetch_row 절대 행)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--XFAIL--
ODBC driver returns garbage bytes for NUMERIC and BIT values.
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[000] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
odbc_exec($conn, 'DROP TABLE IF EXISTS assoc_tb');
odbc_exec($conn, 'CREATE TABLE assoc_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);');
odbc_exec($conn, "INSERT INTO assoc_tb VALUES('string111111','char11111',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
odbc_exec($conn, "INSERT INTO assoc_tb(c1,c2,c3,c4) VALUES('string2222','char22222',2,11.11)");
odbc_exec($conn, "INSERT INTO assoc_tb(c3,c5,c6,c7,c8,c9) VALUES(3,TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31',B'1',513254.3143513)");
odbc_exec($conn, "INSERT INTO assoc_tb(c3,c10,c11) VALUES(4,CHAR_TO_CLOB('This is a Dog2'), BIT_TO_BLOB(X'000010'))");

print("#####positive example#####\n");
$req1 = odbc_exec($conn, 'SELECT c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10) as c10,BLOB_TO_BIT(c11) as c11 FROM assoc_tb ORDER BY c3');
if (!$req1) {
	printf("req1 [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	$row1 = odbc_fetch_array($req1);
	var_dump($row1);
	$cols = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11'];
	$read_row = static function ($req, $rowNum, $cols) {
		if (!odbc_fetch_row($req, $rowNum)) {
			return null;
		}
		$r = [];
		foreach ($cols as $c) {
			$r[$c] = odbc_result($req, $c);
		}
		return $r;
	};
	// cubrid_data_seek($req1, 1) → 2번째 행(1-based)
	$row = $read_row($req1, 2, $cols);
	printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row['c1'], $row['c2'], $row['c3'], $row['c4'], $row['c5'], $row['c6'], $row['c7'], $row['c8'], $row['c9'], $row['c10'], $row['c11']);
	// cubrid_data_seek($req1, 3) → 4번째 행
	$row3 = $read_row($req1, 4, $cols);
	printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row3['c1'], $row3['c2'], $row3['c3'], $row3['c4'], $row3['c5'], $row3['c6'], $row3['c7'], $row3['c8'], $row3['c9'], $row3['c10'], $row3['c11']);
}
odbc_free_result($req1);

print("\n\n#####fetch_row nagetive example#####\n");
$req4 = odbc_exec($conn, 'SELECT c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) FROM assoc_tb ORDER BY c3');
if (!$req4) {
	printf("req4 [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	$row4 = odbc_fetch_array($req4);
	if (!array_key_exists('nothiscolumn', $row4)) {
		printf("[004] Expecting FALSE, got [%d] [%s]\n", odbc_error(), odbc_errormsg());
	} else {
		print($row4['nothiscolumn']);
	}
	if (!array_key_exists(11, $row4)) {
		printf("[005] Expecting FALSE, got [%d] [%s]\n", odbc_error(), odbc_errormsg());
	} else {
		print($row4[11]);
	}
}
odbc_free_result($req4);

$req5 = odbc_exec($conn, 'SELECT c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) FROM assoc_tb WHERE c3 >100 ORDER BY c3');
if (!$req5) {
	printf("req5 [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	$row5 = odbc_fetch_array($req5);
	if (false == $row5) {
		printf("[006] Expecting FALSE, got [%d] [%s]\n", odbc_error(), odbc_errormsg());
	} else {
		printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row5['c1'], $row5['c2'], $row5['c3'], $row5['c4'], $row5['c5'], $row5['c6'], $row5['c7'], $row5['c8'], $row5['c9'], $row5['c10'], $row5['c11']);
	}
}
odbc_free_result($req5);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(11) {
  ["c1"]=>
  string(12) "string111111"
  ["c2"]=>
  string(20) "char11111           "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.1099999999999994"
  ["c5"]=>
  string(8) "02:10:00"
  ["c6"]=>
  string(10) "1977-08-14"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  ["c8"]=>
  string(2) "80"
  ["c9"]=>
  string(11) "432341.4321"
  ["c10"]=>
  string(13) "This is a Dog"
  ["c11"]=>
  string(6) "000001"
}
string2222, char22222           , 2 ,11.110000, , , , , 0.000000, , 
, , 4 ,0.000000, , , , , 0.000000, This is a Dog2, 000010


#####fetch_row nagetive example#####
[004] Expecting FALSE, got [0] []
[005] Expecting FALSE, got [0] []
[006] Expecting FALSE, got [0] []
Finished!
