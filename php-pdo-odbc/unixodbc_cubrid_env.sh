# unixODBC + CUBRID: /etc 와 $HOME 설정을 함께 쓰도록 ODBCSYSINI/ODBCINI 를 맞춘다.
# ~/.odbcinst.ini 에 [CUBRID_ODBC_Unicode] 가 있으면 해당 드라이버가 로드되도록 사용자 디렉터리를 우선한다.
# (그렇지 않으면 /etc/odbcinst.ini 등 기존 순서)
#
# 사용: source "$(dirname "$0")/../unixodbc_cubrid_env.sh"  (경로에 맞게 조정)
# 이미 ODBCSYSINI 또는 ODBCINSTINI 가 설정된 경우에는 건드리지 않는다.

_odbcinst_has_unicode() {
	[[ -f "$1" ]] && grep -q '^\[CUBRID_ODBC_Unicode\]' "$1" 2>/dev/null
}

_odbcinst_has_any_cubrid() {
	[[ -f "$1" ]] && grep -qE '^\[(CUBRID_ODBC|CUBRID_ODBC_ANCI|CUBRID_ODBC_Unicode)\]' "$1" 2>/dev/null
}

_ensure_home_odbcinst_link() {
	if [[ -f "${HOME}/.odbcinst.ini" ]] && [[ ! -e "${HOME}/odbcinst.ini" ]]; then
		ln -sf "${HOME}/.odbcinst.ini" "${HOME}/odbcinst.ini"
	fi
}

if [[ -z "${ODBCSYSINI:-}" ]] && [[ -z "${ODBCINSTINI:-}" ]]; then
	if _odbcinst_has_unicode "${HOME}/.odbcinst.ini"; then
		_ensure_home_odbcinst_link
		export ODBCSYSINI="${HOME}"
	elif _odbcinst_has_any_cubrid /etc/odbcinst.ini; then
		export ODBCSYSINI=/etc
	elif [[ -f "${HOME}/.odbcinst.ini" ]]; then
		_ensure_home_odbcinst_link
		export ODBCSYSINI="${HOME}"
	elif [[ -f /etc/odbcinst.ini ]]; then
		export ODBCSYSINI=/etc
	fi
fi

if [[ -z "${ODBCINI:-}" ]]; then
	if [[ -f /etc/odbc.ini ]] && [[ -f "${HOME}/.odbc.ini" ]]; then
		:
	elif [[ -f /etc/odbc.ini ]]; then
		export ODBCINI=/etc/odbc.ini
	elif [[ -f "${HOME}/.odbc.ini" ]]; then
		export ODBCINI="${HOME}/.odbc.ini"
	fi
fi

# connect.inc / PHP 테스트가 사용하는 드라이버명 (~/.odbcinst.ini 섹션 [CUBRID_ODBC_Unicode] 와 일치)
export CUBRID_ODBC_DRIVER="${CUBRID_ODBC_DRIVER:-CUBRID_ODBC_Unicode}"
