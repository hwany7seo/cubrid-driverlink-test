--TEST--
cubrid_get_charset
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
$db_esc = str_replace("'", "''", $db);
$charset = null;
foreach ([
	"SELECT charset FROM db_root WHERE db_name = '$db_esc'",
	'SELECT charset FROM db_root LIMIT 1',
] as $sql) {
	$r = @odbc_exec($conn, $sql);
	if (!$r || !odbc_fetch_row($r)) {
		continue;
	}
	$charset = (string) odbc_result($r, 1);
	odbc_free_result($r);
	if ($charset !== '') {
		break;
	}
}

if ($charset === null || $charset === '') {
	printf("[002] Could not read charset from db_root\n");
	exit(1);
}

var_dump($charset);

odbc_close($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
string(%d) "%s"
done!
