#!/usr/bin/env bash
# PHP ODBC 변환 테스트 — tests (cubrid-php tests_74 대응). PHP 8.4.19 권장.
# 사용 예: PHP_BIN=/usr/local/php-8.4.19/bin/php ./run_php_odbc_tests.sh
# dnf PHP: TEST_PHP_EXECUTABLE는 절대 경로여야 함(run-tests.php가 file_exists로 검사).
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
	echo "ERROR: PHP 실행 파일을 찾을 수 없습니다: $PHP_CMD (dnf: sudo dnf install -y php-cli)" >&2
	exit 1
}
EXEC_PHP="$(_php_resolve "${TEST_PHP_EXECUTABLE:-$PHP_RESOLVED}")" || {
	echo "ERROR: TEST_PHP_EXECUTABLE를 해석할 수 없습니다: ${TEST_PHP_EXECUTABLE:-}" >&2
	exit 1
}
export TEST_PHP_EXECUTABLE="$EXEC_PHP"
export TEST_PHP_SRCDIR="${TEST_PHP_SRCDIR:-$ROOT/tests}"
cd "$ROOT/tests"
if [ "$#" -eq 0 ]; then
	set -- .
else
	_has_positional=0
	for _a in "$@"; do
		case "$_a" in
		-*) ;;
		*) _has_positional=1; break ;;
		esac
	done
	if [ "$_has_positional" -eq 0 ]; then
		set -- "$@" .
	fi
	unset _a _has_positional
fi
exec "$EXEC_PHP" run-tests.php "$@"
