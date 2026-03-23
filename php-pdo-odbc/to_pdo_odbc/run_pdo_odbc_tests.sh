#!/usr/bin/env bash
# use by PHP 8.4.19
# use by: PHP_BIN=/usr/local/php-8.4.19/bin/php ./run_pdo_odbc_tests.sh
# dnf PHP: TEST_PHP_EXECUTABLE must be an absolute path (run-tests.php checks file_exists).
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
	echo "ERROR: PHP executable not found: $PHP_CMD (dnf: sudo dnf install -y php-cli)" >&2
	exit 1
}

EXEC_PHP="$(_php_resolve "${TEST_PHP_EXECUTABLE:-$PHP_RESOLVED}")" || {
	echo "ERROR: TEST_PHP_EXECUTABLE not found: ${TEST_PHP_EXECUTABLE:-}" >&2
	exit 1
}
export TEST_PHP_EXECUTABLE="$EXEC_PHP"
export TEST_PHP_SRCDIR="${TEST_PHP_SRCDIR:-$ROOT}"

# isql은 로그인 셸의 ODBCINI/ODBCSYSINI를 쓰지만, PHP CLI는 비어 있을 수 있음.
# "Can't open lib 'CUBRID_ODBC_Unicode'" 는 odbcinst.ini를 못 찾거나 Driver= 경로가 잘못된 경우가 많음.
# 필요 시 셸과 동일하게 맞춤 (예: /etc 또는 CUBRID 설치 경로의 setup.sh가 넣는 값).
export ODBCSYSINI="${ODBCSYSINI:-/etc}"
# DSN이 ~/.odbc.ini 또는 /etc/odbc.ini 에만 있을 때 isql과 동일하게 맞추려면 직접 지정:
# export ODBCINI=/etc/odbc.ini
# export ODBCINI=$HOME/.odbc.ini
# libcubrid_odbc.so 의존 라이브러리 — isql만 되고 PHP만 실패할 때:
# export LD_LIBRARY_PATH="/opt/cubrid/lib:${LD_LIBRARY_PATH:-}"
# isql에 쓰는 DSN 이름 그대로 PDO에 쓰려면:
# export PDO_ODBC_TEST_DSN='odbc:DSN=CUBRID_ODBC_Unicode'

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
