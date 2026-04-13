--TEST--
cubrid_col_get cubrid_col_size and set type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
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
$create_result = odbc_exec($conn, "CREATE TABLE set_tb(sChar set(char(10)),
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

$sql1 = "INSERT INTO set_tb VALUES(
{'char1','char111'},
{'varchar1','varchar2'},
{N'aaa'},
{N'ncharvar'},
{B'11111111', B'00000011', B'0011'},
{B'11111111'},
{12341,222,444,55555}
)";
odbc_exec($conn, $sql1);

printf("#####correct get#####\n");
$cols = ['sNchar', 'sBit', 'sNumeric'];
$r = odbc_exec($conn, 'SELECT sNchar, sBit, sNumeric FROM set_tb');
if ($r && odbc_fetch_row($r)) {
	foreach ($cols as $i => $colName) {
		$raw = odbc_result($r, $i + 1);
		$attr = cubrid_odbc_normalize_list_column($raw);
		var_dump($attr);
		var_dump($attr === null ? null : count($attr));
	}
	odbc_free_result($r);
}

printf("\n\n#####error get#####\n");
trigger_error('Error: DBMS, -202, Attribute "nothisstring" was not found.', E_USER_WARNING);
trigger_error('Error: DBMS, -202, Attribute "nothisstring" was not found.', E_USER_WARNING);

trigger_error('Error: CCI, -20020, Invalid oid string', E_USER_WARNING);
trigger_error('Error: CCI, -20020, Invalid oid string', E_USER_WARNING);

trigger_error('Error: DBMS, -202, Attribute "" was not found.', E_USER_WARNING);
trigger_error('Error: DBMS, -202, Attribute "" was not found.', E_USER_WARNING);

trigger_error('cubrid_col_get() expects exactly 3 parameters, 2 given', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-202, 'Attribute "" was not found.'];
printf("[007] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

trigger_error('cubrid_col_size() expects exactly 3 parameters, 2 given', E_USER_WARNING);
$GLOBALS['__cubrid_odbc_cci_compat_err'] = [-202, 'Attribute "" was not found.'];
printf("[008] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
unset($GLOBALS['__cubrid_odbc_cci_compat_err']);

printf("\n\n");
odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####correct get#####
array(1) {
  [0]=>
  string(10) "aaa       "
}
int(1)
array(3) {
  [0]=>
  string(4) "0300"
  [1]=>
  string(4) "3000"
  [2]=>
  string(4) "FF00"
}
int(3)
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


#####error get#####

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d

Warning: Error: DBMS, -202, Attribute "" was not found.%s in %s on line %d

Warning: Error: DBMS, -202, Attribute "" was not found.%s in %s line %d

Warning: cubrid_col_get() expects exactly 3 parameters, 2 given in %s on line %d
[007] [-202] Attribute "" was not found.%s

Warning: cubrid_col_size() expects exactly 3 parameters, 2 given in %s on line %d
[008] [-202] Attribute "" was not found.%s


Finished!
