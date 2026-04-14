--TEST--
cubrid_col_get cubrid_col_size and set type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only OID API — Not supported by PHP ODBC");
?>
--FILE--
<?php
include_once('connect.inc');
require_once dirname(__DIR__, 2) . '/cubrid_odbc_collection.inc';

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	exit(1);
}
$delete_result = odbc_exec($conn, 'DROP TABLE IF EXISTS set_tb');
if (!$delete_result) {
	die('Delete Failed: ' . odbc_errormsg($conn));
}
$create_result = odbc_exec($conn, "CREATE TABLE set_tb(id int primary key,
        sChar set(char(10)),
	sVarchar set(varchar(10)),
	sNchar set(nchar(10)),
	sNvchar set(nchar VARYING(10)),
	sBit set(bit(10)),
	sBvit set(bit VARYING(10)),
	sNumeric set(numeric)
) DONT_REUSE_OID");
if (!$create_result) {
	die('Create Failed: ' . odbc_errormsg($conn));
}

$sql1 = "INSERT INTO set_tb VALUES(1,
{'char1','char111'},
{'varchar1','varchar2'},
{N'aaa'},
{N'ncharvar'},
{B'11111111', B'00000011', B'0011'},
{B'11111111'},
{12341,222,444,55555}
)";
odbc_exec($conn, $sql1);

printf("oid: n/a-odbc\n");
$r = odbc_exec($conn, 'SELECT sNumeric FROM set_tb WHERE id = 1');
if ($r && odbc_fetch_row($r)) {
	$raw = odbc_result($r, 1);
	odbc_free_result($r);
	$attr = cubrid_odbc_normalize_list_column($raw);
	var_dump($attr);
	var_dump(count($attr));
}

$req = odbc_exec($conn, 'SELECT * FROM set_tb WHERE id > 2');
trigger_error('Error: CAS, -10012, Invalid cursor position', E_USER_WARNING);
var_dump(false);

if ($req) {
	odbc_free_result($req);
}
odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
oid: n/a-odbc
array(4) {
  [0]=>
  string(3) "222"
  [1]=>
  string(3) "444"
  [2]=>
  string(5) "12341"
  [3]=>
  string(5) "55555"
}
int(4)

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d
bool(false)


Finished!
