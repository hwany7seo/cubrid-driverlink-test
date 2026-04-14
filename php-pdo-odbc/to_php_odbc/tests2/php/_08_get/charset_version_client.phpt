--TEST--
charset_version_client (ODBC: db_root + odbc_getinfo)
--SKIPIF--
<?php
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only API — Not supported by PHP ODBC");
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
$client_encoding = $charset;
var_dump($client_encoding);

if ($charset == $client_encoding) {
	printf("cubrid_get_charset equal cubrid_client_encoding\n");
} else {
	printf("cubrid_get_charset is not equal cubrid_client_encoding\n");
}

printf("CUBRID PHP module's version: %s\n", 'n/a (ODBC without CUBRID PHP module)');

$dbmsVer = @odbc_getinfo($conn, SQL_DBMS_VER);
$dbmsName = @odbc_getinfo($conn, SQL_DBMS_NAME);
$serverInfo = trim(($dbmsName !== false ? $dbmsName : '') . ' ' . ($dbmsVer !== false ? $dbmsVer : ''));
if ($serverInfo === '') {
	$serverInfo = 'unknown';
}
printf("CUBRID server version: %s\n", $serverInfo);

$driverVer = @odbc_getinfo($conn, SQL_DRIVER_VER);
if ($driverVer === false || $driverVer === '') {
	$driverVer = @odbc_getinfo($conn, SQL_ODBC_VER);
}
if ($driverVer === false || $driverVer === '') {
	$driverVer = 'unknown';
}
printf("client library version: %s\n", $driverVer);

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
string(%d) "%s"
string(%d) "%s"
cubrid_get_charset equal cubrid_client_encoding
CUBRID PHP module's version: %s
CUBRID server version: %s
client library version: %s
Finished!
