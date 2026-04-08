<?php
/**
 * cubrid_connect*.phpt (ODBC 포팅) 공통: DSN 키 치환 및 오류 한 줄 출력.
 */
if (!function_exists('cubrid_connect_test_dsn_kv')) {
	/** @param non-empty-string $dsn */
	function cubrid_connect_test_dsn_kv(string $dsn, string $key, string $value): string
	{
		$k = preg_quote($key, '/');
		if (preg_match('/(^|;)\s*' . $k . '\s*=/i', $dsn)) {
			return preg_replace('/(^|;)\s*' . $k . '\s*=[^;]*/i', '${1}' . $key . '=' . $value, $dsn, 1);
		}
		return $dsn . ';' . $key . '=' . $value;
	}
}

if (!function_exists('cubrid_connect_test_err_line')) {
	function cubrid_connect_test_err_line(string $tag, int $errno, string $msg): void
	{
		$msg = str_replace(["\r", "\n"], ' ', $msg);
		printf("%s %d %s\n", $tag, $errno, $msg);
	}
}
