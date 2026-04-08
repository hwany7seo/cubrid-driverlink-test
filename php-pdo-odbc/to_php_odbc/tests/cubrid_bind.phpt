--TEST--
odbc_execute
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php');
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

@odbc_exec($conn, 'DROP TABLE IF EXISTS bind_test');
odbc_exec($conn, 'CREATE TABLE bind_test(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 datetime)');

/* [002]: wrong bind count on a throwaway statement (not the INSERT used below). */
$req_bad = odbc_prepare($conn, 'INSERT INTO bind_test(c1, c2, c3, c4) VALUES(?, ?, ?, ?)');
if (false !== ($tmp = @odbc_execute($req_bad, array('only')))) {
	printf("[002] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

$req = odbc_prepare($conn, 'INSERT INTO bind_test(c1, c2, c3, c4) VALUES(?, ?, ?, ?)');
if (!odbc_execute($req, array('bind test', 'bind test', 36, 3.6))) {
	printf("[bind] %s\n", odbc_errormsg($conn));
}

$res = odbc_exec($conn, "SELECT c1, c2, c3, CAST(c4 AS VARCHAR(64)) FROM bind_test WHERE c1 = 'bind test'");
if (!$res) {
	var_dump(null);
} else {
	odbc_longreadlen($res, 65536);
	if (!odbc_fetch_row($res)) {
		var_dump(null);
	} else {
		$result = array(
			'c1' => odbc_result($res, 1),
			'c2' => odbc_result($res, 2),
			'c3' => odbc_result($res, 3),
			'c4' => odbc_result($res, 4),
		);
		var_dump($result);
	}
}

$req = odbc_prepare($conn, "INSERT INTO bind_test(c1, c5, c6, c7) VALUES('bind time test', ?, ?, ?)");

if (!odbc_execute($req, array('13:15:45', '2011-03-17', '13:15:45 03/17/2011'))) {
	printf("[bind2] %s\n", odbc_errormsg($conn));
}

$res = odbc_exec($conn, "SELECT c5, c6, CAST(c7 AS VARCHAR(64)) FROM bind_test WHERE c1 = 'bind time test'");
if (!$res) {
	var_dump(null);
} else {
	odbc_longreadlen($res, 65536);
	if (!odbc_fetch_row($res)) {
		var_dump(null);
	} else {
		$result = array(
			'c5' => odbc_result($res, 1),
			'c6' => odbc_result($res, 2),
			'c7' => odbc_result($res, 3),
		);
		var_dump($result);
	}
}

$req_err = odbc_prepare($conn, 'INSERT INTO bind_test(c1, c2) VALUES(?, ?)');
odbc_execute($req_err, array('one'));

odbc_close($conn);

print 'done!';
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
array(4) {
  ["c1"]=>
  string(9) "bind test"
  ["c2"]=>
  string(20) "bind test           "
  ["c3"]=>
  string(2) "36"
  ["c4"]=>
  string(%d) "%s"
}
array(3) {
  ["c5"]=>
  string(8) "13:15:45"
  ["c6"]=>
  string(10) "2011-03-17"
  ["c7"]=>
  string(%d) "%s"
}

Warning: odbc_execute(): %s
done!
