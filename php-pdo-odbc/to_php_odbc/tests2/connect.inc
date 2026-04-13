<?php
$host = getenv('CUBRID_TEST_HOST') ?: 'test-db-server';
$port = (int) (getenv('CUBRID_TEST_PORT') ?: 33000);
$db = getenv('CUBRID_TEST_DB') ?: 'demodb';
$user = getenv('CUBRID_TEST_USER') ?: 'dba';
$passwd = getenv('CUBRID_TEST_PASS') !== false ? getenv('CUBRID_TEST_PASS') : '';
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure = getenv('CUBRID_TEST_SKIP_CONNECT_FAILURE') !== '0';

$dsn_override = getenv('PHP_ODBC_TEST_DSN');
if ($dsn_override !== false && $dsn_override !== '') {
	$cubrid_odbc_dsn = $dsn_override;
} else {
	$driver = getenv('CUBRID_ODBC_DRIVER');
	if ($driver === false || $driver === '') {
		$driver = 'CUBRID_ODBC_Unicode';
	}
	// CUBRID ODBC 키워드는 DB_NAME (database= 아님) — CCI -20030 Invalid url 방지
	$cubrid_odbc_dsn = sprintf(
		'Driver={%s};server=%s;port=%d;uid=%s;pwd=%s;DB_NAME=%s',
		$driver,
		$host,
		$port,
		$user,
		$passwd,
		$db
	);
}
require_once __DIR__ . '/../cubrid_odbc_compat.inc';
