https://github.com/hwany7seo/cubrid-driverlink-test

# 회의 관련
## 검토 요청 사항
- 현재 선정된 JDBC, ODBC Wrapper 드라이버를 더 좋은 드라이버로 대체하는 것에 대한 검토.
- 실제 사용자용에 대한 Setup 가이드
- 실제 사용 사례나 누락된 Function 될 만한 테스트 케이스 추가 ?
- CUBRID 특성상 꼭 지원해야 하지만 ODBC에서 지원하지 않거나? Wrapper 드라이버는 지원을 하는데 ODBC에서 지원하지 못하는 것들 검토 (Last id 같은 것들).
  - ODBC 지원하지 않아서 동작 할 수 없는 기능
  - Wrapper Driver 지원을 안해서 쓸 수 기능들 (JDBC에서 ODBC 스펙에서 지원해야하는 것들 만 해당됨.)
  - 기존 드라이버들에서 지원을 했는데 못쓰는 것들 (이런 목록 어떤 식든 유저 지원할 수 있게 해야됨.)
  - 검토 일정 논의 (추후 JIRA 통해)

## 회의 안건
- 매뉴얼, function list 관련
  - 기존 사용자용
    - 매뉴얼이나 function list를 맵핑 가이드가 필요 할 것 같음.
  - 신규는 매뉴얼 추가(function list들)

- 각 드라이버에 대한 현재 관리 사이트 검토 필요 (청와대 - PHP(linux 인지 windows인지 확인 필요), 육군 - Go Driver, 외교부 - python)

- 각 언어들에 대한 버전이 업그레이드 되었을 경우 호환성 문제가 있을 수 있음. (김정훈님 의견)
- 추후 Driver 관리 관련
 - 기존 사이트 이슈는 이슈가 있을때만 관리.
 - 신규 사이트는 Wrapper 드라이버로 권장.

# Test Issue	
	
## GO ODBC (https://github.com/alexbrainman/odbc)
1. BIT TYPE, SET TYPE이 Insert 되지 않는 문제 확인 필요.

## Nodejs-JDBC (https://github.com/pueteam/nodejs-jdbc)
1. Rollback() (savepoint 미지원) 
2. 변경 선정 진행
https://github.com/pueteam/nodejs-jdbc

## Perl- ODBC (DBI -> cubrid-perl, DBI -> DBI-ODBC - https://github.com/perl5-dbi/DBD-ODBC)
1. 테스트 전환 시 문제점 : 
-- last_insert_id 미지원 -> DBI는 지원하나 DBD:ODBC 미구현되어 있으며 ODBC 또한 별도 API를 제공하지 않으므로 쿼리를 통해 사용해야 함.
2. VARCHAR 빈 문자열과 NUL 문자 (40bindparam.t, 40keyinfo.t,  40nulls_prepare.t, 40tableinfo.t)
3.  -20008 — Type conversion error (bind_enum_apis-341.t)

## PHP, PDO ODBC (https://github.com/php/php-src/tree/master/ext)
- odbc_bindcols에서 String이나 VARCHAR 큰 타입일 경우 VARCHAR로 보고한 후 1G의 크기를 보내므로 1G의 가까운 크기를 잡에 매번 오류가 발생합니다. (타 ODBC와 비교하여 수정 필요)
- SQLExtendedFetch관련 지원type 정보가 잘못되어 있음
- SQLColumnPrivileges 구현 필요.
- odbc_fetch_array에서 ENUM, NUMERIC, BIT, SET 타입 garabe 오류
	

## PyODBC (tests, tests2, test3) - https://github.com/mkleehammer/pyodbc
- Tests2에서 error 코드 테스트가 많으나 pyodbc에서 보내는 error외에 
ODBC에서 보내는 코드에 대해서 오류 메세지가 미흡함 확인 후 수정필요 (execute_issue, execute_view in test2)
- cur.setinputsizes([(SQL_BLOB, len(img_data), 0)])
- Blob, Clob 관련 오류 (테스트 버전 수정 확인 후 반영 예정)

## Ruby (https://github.com/larskanis/ruby-odbc)
- DATE TYPE으로 Insert 할수 없는 문제가 있으나 문자열로 사용 시 충분히 호환이 가능하여
코드만 분석 후에 ruby_date_issue branch에 정리


