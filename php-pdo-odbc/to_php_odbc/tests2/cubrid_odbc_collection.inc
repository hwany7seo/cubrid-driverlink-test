<?php
/**
 * tests2 전용: LIST/SET 컬럼 ODBC 문자열을 네이티브 cubrid_col_get 과 유사한 문자열 배열로 정규화.
 * tests/cubrid_seq_drop.phpt 등과 동일 로직.
 */
if (!function_exists('cubrid_odbc_normalize_list_column')) {
	function cubrid_odbc_normalize_list_column($v)
	{
		if (is_array($v)) {
			$out = [];
			foreach ($v as $x) {
				$out[] = (string) $x;
			}
			return $out;
		}
		if (!is_string($v)) {
			return null;
		}
		$v = trim($v);
		if (strlen($v) < 2 || $v[0] !== '{') {
			return null;
		}
		$inner = substr($v, 1, -1);
		if ($inner === '') {
			return [];
		}
		$out = [];
		foreach (explode(',', $inner) as $p) {
			$out[] = trim($p);
		}
		return $out;
	}
}
