#!/usr/bin/env bash
# PHP 8.4+ / PDO_ODBC: cubrid-pdo tests_74와 동일한 .phpt를 tests/에서 실행합니다.
# 예: PHP_BIN=/usr/local/php-8.4.19/bin/php ./run_pdo_odbc_tests.sh
# TEST_PHP_EXECUTABLE는 절대 경로여야 합니다 (run-tests.php가 file_exists로 검사).
#
# unixODBC: 시스템(/etc)과 사용자($HOME) 설정 모두 지원.
# - 이미 ODBCSYSINI / ODBCINI / ODBCINSTINI 를 쓰는 경우 그대로 둠.
# - ODBCSYSINI 미지정 시: /etc/odbcinst.ini 에 CUBRID 드라이버가 있으면 ODBCSYSINI=/etc,
#   없고 ~/.odbcinst.ini 만 있으면 unixODBC 규칙($ODBCSYSINI/odbcinst.ini, 점 없음)에 맞게
#   ~/odbcinst.ini → ~/.odbcinst.ini 심볼릭 링크 후 ODBCSYSINI=$HOME.
# - ODBCINI 미지정 시: /etc/odbc.ini 와 ~/.odbc.ini 가 둘 다 있으면 비워 두어 기본 검색(병합),
#   하나만 있으면 그 경로만 지정.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
_php_resolve() {
	if [[ "${1:-}" == /* ]]; then
		printf '%s' "$1"
	else
		command -v "$1"
	fi
}
PHP_CMD="${PHP_BIN:-php}"
PHP_RESOLVED="$(_php_resolve "$PHP_CMD")" || {
	echo "ERROR: PHP executable not found: $PHP_CMD" >&2
	exit 1
}
EXEC_PHP="$(_php_resolve "${TEST_PHP_EXECUTABLE:-$PHP_RESOLVED}")" || {
	echo "ERROR: TEST_PHP_EXECUTABLE not found: ${TEST_PHP_EXECUTABLE:-}" >&2
	exit 1
}
export TEST_PHP_EXECUTABLE="$EXEC_PHP"
export TEST_PHP_SRCDIR="${TEST_PHP_SRCDIR:-$ROOT}"

_odbcinst_has_cubrid() {
	[[ -f "$1" ]] && grep -qE '^\[(CUBRID_ODBC|CUBRID_ODBC_ANCI|CUBRID_ODBC_Unicode)\]' "$1" 2>/dev/null
}

if [[ -z "${ODBCSYSINI:-}" ]] && [[ -z "${ODBCINSTINI:-}" ]]; then
	if _odbcinst_has_cubrid /etc/odbcinst.ini; then
		export ODBCSYSINI=/etc
	elif [[ -f "${HOME}/.odbcinst.ini" ]]; then
		if [[ ! -e "${HOME}/odbcinst.ini" ]]; then
			ln -sf "${HOME}/.odbcinst.ini" "${HOME}/odbcinst.ini"
		fi
		export ODBCSYSINI="${HOME}"
	elif [[ -f /etc/odbcinst.ini ]]; then
		export ODBCSYSINI=/etc
	fi
fi

if [[ -z "${ODBCINI:-}" ]]; then
	if [[ -f /etc/odbc.ini ]] && [[ -f "${HOME}/.odbc.ini" ]]; then
		: # 둘 다 있으면 미설정 → Driver Manager가 시스템/사용자 DSN 모두 사용
	elif [[ -f /etc/odbc.ini ]]; then
		export ODBCINI=/etc/odbc.ini
	elif [[ -f "${HOME}/.odbc.ini" ]]; then
		export ODBCINI="${HOME}/.odbc.ini"
	fi
fi

# 비대화형(파이프/CI)에서는 QA 메일 프롬프트 방지
if [[ -z "${NO_INTERACTION:-}" ]] && [[ ! -t 0 ]]; then
	export NO_INTERACTION=1
fi

cd "$ROOT"
if [ "$#" -eq 0 ]; then
	set -- "$ROOT/tests"
else
	_has_positional=0
	for _a in "$@"; do
		case "$_a" in
		-*) ;;
		*) _has_positional=1; break ;;
		esac
	done
	if [ "$_has_positional" -eq 0 ]; then
		set -- "$@" "$ROOT/tests"
	fi
	unset _a _has_positional
fi
exec "$EXEC_PHP" tests/run-tests.php "$@"
