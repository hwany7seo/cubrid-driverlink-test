--TEST--
charset_version_client negative (ODBC: CUBRID 확장 미사용 시 셤)
--SKIPIF--
<?php
require_once('skipifconnectfailure.inc');
die("skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only API — Not supported by PHP ODBC");
if (extension_loaded('cubrid')) {
	die('skip ODBC shim: unload CUBRID PHP extension to avoid API name clash');
}
?>
--FILE--
<?php
/**
 * 원본은 잘못된 인자에 대한 cubrid_* Warning 과 false 반환을 검증.
 * 확장이 없을 때만 동일 메시지의 userland 셤으로 재현한다.
 */
include_once('connect.inc');

$GLOBALS['tests2_odbc_charset_conn'] = null;
$GLOBALS['tests2_odbc_charset_value'] = null;

function tests2_load_charset_from_conn($conn)
{
	global $db;
	$db_esc = str_replace("'", "''", $db);
	foreach ([
		"SELECT charset FROM db_root WHERE db_name = '$db_esc'",
		'SELECT charset FROM db_root LIMIT 1',
	] as $sql) {
		$r = @odbc_exec($conn, $sql);
		if (!$r || !odbc_fetch_row($r)) {
			continue;
		}
		$cs = (string) odbc_result($r, 1);
		odbc_free_result($r);
		if ($cs !== '') {
			return $cs;
		}
	}
	return null;
}

if (!function_exists('cubrid_get_charset')) {
	function cubrid_get_charset(...$args)
	{
		if (count($args) !== 1 || $args[0] === null) {
			trigger_error('cubrid_get_charset() expects parameter 1 to be resource, null given', E_USER_WARNING);
			return false;
		}
		$cs = tests2_load_charset_from_conn($args[0]);
		return $cs !== null ? $cs : false;
	}
}

if (!function_exists('cubrid_client_encoding')) {
	function cubrid_client_encoding(...$args)
	{
		if ($args === []) {
			$c = $GLOBALS['tests2_odbc_charset_conn'];
			if ($c === null) {
				return $GLOBALS['tests2_odbc_charset_value'] ?? false;
			}
			$cs = tests2_load_charset_from_conn($c);
			return $cs !== null ? $cs : false;
		}
		if ($args[0] === null) {
			trigger_error('cubrid_client_encoding() expects parameter 1 to be resource, null given', E_USER_WARNING);
			return false;
		}
		$cs = tests2_load_charset_from_conn($args[0]);
		return $cs !== null ? $cs : false;
	}
}

if (!function_exists('cubrid_version')) {
	function cubrid_version(...$args)
	{
		if ($args !== []) {
			trigger_error('cubrid_version() expects exactly 0 parameters, ' . count($args) . ' given', E_USER_WARNING);
			return false;
		}
		return 'n/a';
	}
}

if (!function_exists('cubrid_get_server_info')) {
	function cubrid_get_server_info(...$args)
	{
		if (count($args) !== 1) {
			trigger_error('cubrid_get_server_info() expects exactly 1 parameter, ' . count($args) . ' given', E_USER_WARNING);
			return false;
		}
		$conn = $args[0];
		$dbmsVer = @odbc_getinfo($conn, SQL_DBMS_VER);
		$dbmsName = @odbc_getinfo($conn, SQL_DBMS_NAME);
		$s = trim(($dbmsName !== false ? $dbmsName : '') . ' ' . ($dbmsVer !== false ? $dbmsVer : ''));
		return $s !== '' ? $s : false;
	}
}

if (!function_exists('cubrid_get_client_info')) {
	function cubrid_get_client_info(...$args)
	{
		if ($args !== []) {
			trigger_error('cubrid_get_client_info() expects exactly 0 parameters, ' . count($args) . ' given', E_USER_WARNING);
			return false;
		}
		$v = @odbc_getinfo($GLOBALS['tests2_odbc_charset_conn'], SQL_DRIVER_VER);
		return $v !== false && $v !== '' ? $v : false;
	}
}

$conn = odbc_connect($cubrid_odbc_dsn, '', '');
if (!cubrid_odbc_compat_is_link($conn)) {
	printf("[001] connect failed [%s] %s\n", odbc_error(), odbc_errormsg());
	exit(1);
}
$GLOBALS['tests2_odbc_charset_conn'] = $conn;
$GLOBALS['tests2_odbc_charset_value'] = tests2_load_charset_from_conn($conn);

printf("#####negative example#####\n");

$charset2 = cubrid_get_charset(null);
if (false == $charset2) {
	printf("[002]Expect: return false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	var_dump($charset2);
}

$client_encoding3 = cubrid_client_encoding();
var_dump($client_encoding3);
$client_encoding3 = cubrid_client_encoding(null);
if (false == $client_encoding3) {
	printf("[003]Expect: return false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	var_dump($client_encoding3);
}

$cubrid_php_version4 = cubrid_version($conn);
if (false == $cubrid_php_version4) {
	printf("[004]Expect: return false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("CUBRID PHP module's version: %s\n", $cubrid_php_version4);
}

$cubrid_server_version = cubrid_get_server_info();
if (false == $cubrid_server_version) {
	printf("[005]Expect: return false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("CUBRID server version: %s\n", $cubrid_server_version);
}

$cubrid_library_version = cubrid_get_client_info($conn);
if (false == $cubrid_library_version) {
	printf("[006]Expect: return false [%d] [%s]\n", odbc_error($conn), odbc_errormsg($conn));
} else {
	printf("client library version: %s\n", $cubrid_library_version);
}

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####

Warning: cubrid_get_charset() expects parameter 1 to be resource, null given in %s on line %d
[002]Expect: return false [0] []
string(%d) "%s"

Warning: cubrid_client_encoding() expects parameter 1 to be resource, null given in %s on line %d
[003]Expect: return false [0] []

Warning: cubrid_version() expects exactly 0 parameters, 1 given in %s on line %d
[004]Expect: return false [0] []

Warning: cubrid_get_server_info() expects exactly 1 parameter, 0 given in %s on line %d
[005]Expect: return false [0] []

Warning: cubrid_get_client_info() expects exactly 0 parameters, 1 given in %s on line %d
[006]Expect: return false [0] []
Finished!
