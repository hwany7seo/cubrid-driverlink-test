<?php
/**
 * cubrid-php 레거시 호출명을 ext/odbc + CUBRID ODBC 조합에서 쓰기 위한 최소 호환 레이어 (PHP 8.4).
 * 완전한 CCI/확장 API 대체는 아니며, 드라이버 전용 동작이 필요한 테스트는 별도 스킵/정리가 필요합니다.
 */
if (!defined('CUBRID_NUM')) {
	define('CUBRID_NUM', 1);
}
if (!defined('CUBRID_ASSOC')) {
	define('CUBRID_ASSOC', 2);
}
if (!defined('CUBRID_BOTH')) {
	define('CUBRID_BOTH', 3);
}
if (!defined('CUBRID_OBJECT')) {
	define('CUBRID_OBJECT', 4);
}
if (!defined('CUBRID_AUTOCOMMIT_FALSE')) {
	define('CUBRID_AUTOCOMMIT_FALSE', 0);
}
if (!defined('CUBRID_AUTOCOMMIT_TRUE')) {
	define('CUBRID_AUTOCOMMIT_TRUE', 1);
}
if (!defined('CUBRID_EXEC_QUERY_ALL')) {
	define('CUBRID_EXEC_QUERY_ALL', 8);
}

if (!isset($GLOBALS['__cubrid_odbc_bind_params']) || !is_array($GLOBALS['__cubrid_odbc_bind_params'])) {
	$GLOBALS['__cubrid_odbc_bind_params'] = [];
}

if (!function_exists('cubrid_odbc_compat_is_link')) {
	function cubrid_odbc_compat_is_link(mixed $v): bool
	{
		if ($v === null || $v === false) {
			return false;
		}
		if (class_exists('Odbc\\Connection', false) && $v instanceof \Odbc\Connection) {
			return true;
		}
		return is_resource($v);
	}
}

if (!function_exists('cubrid_odbc_compat_is_result')) {
	function cubrid_odbc_compat_is_result(mixed $v): bool
	{
		if ($v === null || $v === false) {
			return false;
		}
		if (class_exists('Odbc\\Result', false) && $v instanceof \Odbc\Result) {
			return true;
		}
		return is_resource($v);
	}
}

if (!function_exists('cubrid_odbc_compat_parse_errno')) {
	function cubrid_odbc_compat_parse_errno(string $sqlstate, string $msg): int
	{
		if (preg_match('/\[(-?\d+)\]/', $msg, $m)) {
			return (int) $m[1];
		}
		if ($sqlstate !== '' && ctype_digit(str_replace(['-', '+'], '', $sqlstate))) {
			return (int) $sqlstate;
		}
		return 0;
	}
}

if (!function_exists('cubrid_odbc_compat_odbc_errno_msg')) {
	function cubrid_odbc_compat_odbc_errno_msg(mixed $conn): array
	{
		if ($conn !== null && $conn !== false) {
			$st = odbc_error($conn);
			$msg = odbc_errormsg($conn);
		} else {
			$st = odbc_error();
			$msg = odbc_errormsg();
		}
		$st = is_string($st) ? $st : '';
		$msg = is_string($msg) ? $msg : '';
		return [cubrid_odbc_compat_parse_errno($st, $msg), $msg];
	}
}

if (!function_exists('cubrid_odbc_set_last_connection')) {
	function cubrid_odbc_set_last_connection(mixed $conn): void
	{
		if (cubrid_odbc_compat_is_link($conn)) {
			$GLOBALS['__cubrid_odbc_last_conn'] = $conn;
		}
	}
}

if (!function_exists('cubrid_stmt_key')) {
	function cubrid_stmt_key($stmt): string
	{
		if (is_object($stmt)) {
			return 'o' . spl_object_id($stmt);
		}
		return 'r' . (int) $stmt;
	}
}

if (!function_exists('cubrid_bind')) {
	function cubrid_bind($stmt, ...$args)
	{
		if ($stmt === null || $stmt === false) {
			return null;
		}
		if (count($args) < 2) {
			return false;
		}
		$idx = $args[0];
		$val = $args[1];
		if (!is_int($idx) && !(is_string($idx) && ctype_digit((string) $idx))) {
			return false;
		}
		$i = (int) $idx;
		if ($i < 1) {
			return null;
		}
		if ($i > 32) {
			return false;
		}
		$key = cubrid_stmt_key($stmt);
		if (!isset($GLOBALS['__cubrid_odbc_bind_params'][$key])) {
			$GLOBALS['__cubrid_odbc_bind_params'][$key] = [];
		}
		$GLOBALS['__cubrid_odbc_bind_params'][$key][$i - 1] = $val;
		return true;
	}
}

if (!function_exists('cubrid_odbc_execute_bound')) {
	function cubrid_odbc_execute_bound($stmt)
	{
		$key = cubrid_stmt_key($stmt);
		$map = $GLOBALS['__cubrid_odbc_bind_params'][$key] ?? null;
		if ($map === null || $map === []) {
			return odbc_execute($stmt);
		}
		ksort($map, SORT_NUMERIC);
		$maxIdx = (int) max(array_keys($map));
		$params = [];
		for ($i = 0; $i <= $maxIdx; $i++) {
			$params[] = array_key_exists($i, $map) ? $map[$i] : null;
		}
		$ok = odbc_execute($stmt, $params);
		if ($ok !== false) {
			unset($GLOBALS['__cubrid_odbc_bind_params'][$key]);
		}
		return $ok;
	}
}

if (!function_exists('cubrid_errno')) {
	function cubrid_errno($conn = null)
	{
		$argc = func_num_args();
		if ($argc >= 2) {
			return null;
		}
		if ($argc === 0) {
			$lc = $GLOBALS['__cubrid_odbc_last_conn'] ?? null;
			if (!cubrid_odbc_compat_is_link($lc)) {
				return false;
			}
			try {
				[$n,] = cubrid_odbc_compat_odbc_errno_msg($lc);
				return $n;
			} catch (Throwable $e) {
				trigger_error('cubrid_errno(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
				return false;
			}
		}
		if ($conn === null) {
			return null;
		}
		if (!cubrid_odbc_compat_is_link($conn)) {
			trigger_error('cubrid_errno(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
			return false;
		}
		try {
			[$n,] = cubrid_odbc_compat_odbc_errno_msg($conn);
			return $n;
		} catch (Throwable $e) {
			trigger_error('cubrid_errno(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
			return false;
		}
	}
}

if (!function_exists('cubrid_error')) {
	function cubrid_error($conn = null)
	{
		$argc = func_num_args();
		if ($argc >= 2) {
			return null;
		}
		if ($argc === 0) {
			$lc = $GLOBALS['__cubrid_odbc_last_conn'] ?? null;
			if (!cubrid_odbc_compat_is_link($lc)) {
				return false;
			}
			try {
				[, $msg] = cubrid_odbc_compat_odbc_errno_msg($lc);
				return $msg;
			} catch (Throwable $e) {
				trigger_error('cubrid_error(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
				return false;
			}
		}
		if ($conn === null) {
			return null;
		}
		if (!cubrid_odbc_compat_is_link($conn)) {
			trigger_error('cubrid_error(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
			return false;
		}
		try {
			[, $msg] = cubrid_odbc_compat_odbc_errno_msg($conn);
			return $msg;
		} catch (Throwable $e) {
			trigger_error('cubrid_error(): supplied resource is not a valid CUBRID Connect resource', E_USER_WARNING);
			return false;
		}
	}
}

if (!function_exists('cubrid_disconnect')) {
	function cubrid_disconnect($conn)
	{
		if (isset($GLOBALS['__cubrid_odbc_last_conn']) && $GLOBALS['__cubrid_odbc_last_conn'] === $conn) {
			unset($GLOBALS['__cubrid_odbc_last_conn'], $GLOBALS['__cubrid_odbc_last_result']);
		}
		return odbc_close($conn);
	}
}

if (!function_exists('cubrid_query')) {
	/**
	 * cubrid_query(string $sql [, $conn]) 또는 잘못 변환된 cubrid_query($conn, string $sql) 모두 허용.
	 * 연결이 생략되면 마지막으로 등록된 연결(__cubrid_odbc_last_conn)을 사용.
	 * 인자 0개·불필요한 추가 인자는 cubrid-php 와 같이 null 을 반환한다.
	 */
	function cubrid_query($sqlOrConn = null, $connOrSql = null, mixed ...$extra)
	{
		if (func_num_args() === 0) {
			return null;
		}
		if (count($extra) > 0) {
			return null;
		}
		$a = $sqlOrConn;
		$b = $connOrSql;
		if (cubrid_odbc_compat_is_link($a) && is_string($b)) {
			$sql = $b;
			$conn = $a;
		} elseif (is_string($a)) {
			$sql = $a;
			$conn = $b;
			if (($conn === null || $conn === false) && isset($GLOBALS['__cubrid_odbc_last_conn'])
				&& cubrid_odbc_compat_is_link($GLOBALS['__cubrid_odbc_last_conn'])) {
				$conn = $GLOBALS['__cubrid_odbc_last_conn'];
			}
		} else {
			return false;
		}
		if (!cubrid_odbc_compat_is_link($conn)) {
			return false;
		}
		$GLOBALS['__cubrid_odbc_last_conn'] = $conn;
		$res = odbc_exec($conn, $sql);
		if ($res !== false) {
			$GLOBALS['__cubrid_odbc_last_result'] = $res;
		}
		return $res;
	}
}

if (!function_exists('cubrid_fetch')) {
	/**
	 * cubrid_fetch([$result [, $mode]]) — 인자 생략 시 마지막 쿼리 결과(cubrid_query/odbc_exec 후)를 사용.
	 * 인자 1개이고 값이 fetch 모드 상수(1~4)이면 결과 핸들 없이 모드만 지정한 호출로 본다.
	 */
	function cubrid_fetch($result = null, $mode = CUBRID_BOTH)
	{
		$argc = func_num_args();
		if ($argc === 0) {
			$result = $GLOBALS['__cubrid_odbc_last_result'] ?? null;
			$mode = CUBRID_BOTH;
			if ($result === null || $result === false) {
				return null;
			}
		} elseif ($argc === 1 && is_int($result) && $result >= CUBRID_NUM && $result <= CUBRID_OBJECT) {
			$mode = $result;
			$result = $GLOBALS['__cubrid_odbc_last_result'] ?? null;
			if ($result === null || $result === false) {
				return null;
			}
		} elseif ($argc === 1 && cubrid_odbc_compat_is_link($result)) {
			return false;
		}
		if ($result === false || $result === null) {
			return false;
		}
		if (!cubrid_odbc_compat_is_result($result)) {
			return false;
		}
		$row = odbc_fetch_array($result);
		if ($row === false) {
			return false;
		}
		switch ($mode) {
			case CUBRID_ASSOC:
				return $row;
			case CUBRID_NUM:
				return array_values($row);
			case CUBRID_OBJECT:
				return (object) $row;
			case CUBRID_BOTH:
			default:
				$both = [];
				$i = 0;
				foreach ($row as $k => $v) {
					$both[$i] = $v;
					$both[$k] = $v;
					$i++;
				}
				return $both;
		}
	}
}

if (!function_exists('cubrid_fetch_assoc')) {
	function cubrid_fetch_assoc($result)
	{
		if ($result === false || $result === null) {
			return false;
		}
		return odbc_fetch_array($result);
	}
}

if (!function_exists('cubrid_fetch_lengths')) {
	function cubrid_fetch_lengths($result)
	{
		if ($result === false || $result === null) {
			return false;
		}
		$n = odbc_num_fields($result);
		if ($n === false || $n < 1) {
			return false;
		}
		$lens = [];
		for ($i = 1; $i <= $n; $i++) {
			try {
				$v = odbc_result($result, $i);
			} catch (Throwable $e) {
				return false;
			}
			if ($v === false) {
				$v = '';
			}
			$lens[] = strlen((string) $v);
		}
		return $lens;
	}
}

if (!function_exists('cubrid_free_result')) {
	function cubrid_free_result($result)
	{
		if ($result === false || $result === null) {
			return false;
		}
		if (isset($GLOBALS['__cubrid_odbc_last_result']) && $GLOBALS['__cubrid_odbc_last_result'] === $result) {
			unset($GLOBALS['__cubrid_odbc_last_result']);
		}
		return odbc_free_result($result);
	}
}

if (!function_exists('cubrid_execute')) {
	function cubrid_execute($stmt)
	{
		return cubrid_odbc_execute_bound($stmt);
	}
}

if (!function_exists('cubrid_get_autocommit')) {
	function cubrid_get_autocommit($conn)
	{
		if (!cubrid_odbc_compat_is_link($conn)) {
			return false;
		}
		try {
			return odbc_autocommit($conn);
		} catch (Throwable $e) {
			return false;
		}
	}
}

if (!function_exists('cubrid_set_autocommit')) {
	function cubrid_set_autocommit($conn, $mode)
	{
		if (cubrid_odbc_compat_is_link($conn)) {
			$GLOBALS['__cubrid_odbc_last_conn'] = $conn;
		}
		return odbc_autocommit($conn, (bool) $mode);
	}
}

if (!function_exists('cubrid_real_escape_string')) {
	function cubrid_real_escape_string(string $str, $conn = null): string
	{
		return str_replace("'", "''", $str);
	}
}

if (!function_exists('cubrid_num_cols')) {
	function cubrid_num_cols($result)
	{
		if ($result === false || $result === null) {
			return -1;
		}
		$n = odbc_num_fields($result);
		return $n !== false ? (int) $n : -1;
	}
}

if (!function_exists('cubrid_num_fields')) {
	function cubrid_num_fields($result)
	{
		return cubrid_num_cols($result);
	}
}

if (!function_exists('cubrid_column_names')) {
	function cubrid_column_names($result)
	{
		if ($result === false || $result === null) {
			return false;
		}
		$n = odbc_num_fields($result);
		if ($n === false || $n < 1) {
			return false;
		}
		$names = [];
		for ($i = 1; $i <= $n; $i++) {
			$names[] = odbc_field_name($result, $i);
		}
		return $names;
	}
}

if (!function_exists('cubrid_column_types')) {
	function cubrid_column_types($result)
	{
		if ($result === false || $result === null) {
			return false;
		}
		$n = odbc_num_fields($result);
		if ($n === false || $n < 1) {
			return false;
		}
		$types = [];
		for ($i = 1; $i <= $n; $i++) {
			$types[] = odbc_field_type($result, $i);
		}
		return $types;
	}
}

if (!function_exists('cubrid_field_len')) {
	function cubrid_field_len($result, int $field_offset_0)
	{
		if ($result === false || $result === null) {
			return false;
		}
		$len = odbc_field_len($result, $field_offset_0 + 1);
		return $len !== false ? $len : -1;
	}
}

if (!function_exists('cubrid_field_seek')) {
	function cubrid_field_seek($result, int $field_offset_0): bool
	{
		return $result !== false && $result !== null;
	}
}

if (!function_exists('cubrid_fetch_field')) {
	function cubrid_fetch_field($result, int $field_offset_0 = 0)
	{
		if ($result === false || $result === null) {
			return false;
		}
		$name = odbc_field_name($result, $field_offset_0 + 1);
		if ($name === false) {
			return false;
		}
		$o = new stdClass();
		$o->name = $name;
		$o->table = '';
		$o->def = '';
		$o->max_length = (int) odbc_field_len($result, $field_offset_0 + 1);
		$o->not_null = 0;
		$o->primary_key = 0;
		$o->unique_key = 0;
		$o->multiple_key = 0;
		$t = odbc_field_type($result, $field_offset_0 + 1);
		$o->numeric = is_string($t) && stripos($t, 'int') !== false ? 1 : 0;
		$o->blob = is_string($t) && stripos($t, 'blob') !== false ? 1 : 0;
		return $o;
	}
}
