본 테스트는 PHP(https://github.com/php/php-src/) 내부 Module을 통해 cubrid ODBC를 호출하는 형태로 사용됨.
PHP의 내부 Module은 ODBC, PDO는 PDO-ODBC를 사용합니다.

본 테스트는 아래와 같은 환경에서 테스트 됨
- PHP 8.4.19 (https://www.php.net/)
- PHP ODBC Module (https://github.com/php/php-src/tree/master/ext/odbc)
- PHP PDO-ODBC Module (https://github.com/php/php-src/tree/master/ext/pdo_odbc)
- CUBRID ODBC 11.4.1.0101

환경 설정 (소스로 설치 시)
1. PHP 설치
2. php.ini 수정 (extension 추가)
(for php_odbc.dll) extension=odbc
(for php_pdo_odbc.dll) extension=php_pdo_odbc.dll

환경 설정 (use linux Package)
1. PHP 설치
```
sudo dnf module enable php:remi-8.4 -y
sudo dnf install -y php-cli php-pdo php-odbc unixODBC
2. ini 수정 및 추가
20-odbc.ini -> extension=odbc
30-pdo_odbc.init -> extension=pdo_odbc
```
모든 Test는 'test-db-server' hostname으로 테스트 됩니다.

테스트 방법 
- Windows
```
1. odbc 관리자 설정
2. connectODBC의 설정에 맞게 connection url 변경 필요.
3. PHP 설치 폴더에 맞게 'run_tests.bat' 안에 PHP_PATH 변수 수정.
4. 'run_tests.bat' 실행 
```
- Linux
1. unixODBC 설치
2. odbc 관리자 설정 (~/.odbc.ini and cat ~/.odbcinst.ini)
```
[hwanyseo@hwanyseo-3-31 ~]$ cat ~/.odbc.ini 
[CUBRID_ANCI]
Driver = CUBRID_ODBC_ANCI
Description = CUBRID ODBC ANCI
DB_NAME = demodb
UID = dba
PWD =
SERVER = test-db-server
PORT = 33000
FETCH_SIZE = 1
AUTOCOMMIT = false
OMIT_SCHEMA = no
CHARSET = utf8

[CUBRID_Unicode]
Driver = CUBRID_ODBC_Unicode
Description = CUBRID ODBC Unicode
DB_NAME = demodb
UID = dba
PWD =
SERVER = test-db-server
PORT = 33000
FETCH_SIZE = 1
AUTOCOMMIT = false
OMIT_SCHEMA = no
CHARSET = utf8
```

```
[hwanyseo@hwanyseo-3-31 ~]$ cat ~/.odbcinst.ini 
[CUBRID_ODBC_ANCI]
Description = CUBRID Linux ODBC Driver
Driver = /home/hwanyseo/cubrid-odbc/lib/libcubrid-odbc.so
FileUsage = 1
[CUBRID_ODBC_Unicode]
Description = CUBRID Linux ODBC Unicode Driver
Driver = /home/hwanyseo/cubrid-odbc/lib/libcubrid-odbcw.so
IANAAppCodePage = 1
```
- 기본 테스트
  - ./test_linux.sh (basic test - php & pdo)
- 테스트 케이스 변환 검증
  - ./to_php_odbc/run_php_odbc_tests.sh (php tests)
  - ./to_php_odbc/run_php_odbc_tests2.sh (php tests2)
  - ./to_pdo_odbc/run_pdo_odbc_tests.sh (pdo tests)

파일 정보
- php_pdo_odbc_prepare.php (/ext/php_pdo_odbc.dll 사용 예제)
- php_odbc_test_prepare.php (/ext/php_odbc.dll 사용 예제)

테스트 케이스 변환 검증 Known Issue 
- SQLFetchScroll 지원 관련 SQLGetInfo 의 SQL_FETCH_DIRECTION 수정 필요 (_02_prepare | data_seek.phpt, data_seek_02.phpt)
- SQLColumnPrivileges 미지원 (_03_close | connect_01.phpt)
- ENUM, Decimal, NUMERIC, BIT/BINARY/ BLOB 등 결과 값의 바이너리 데이터 문제 MySQL와 비교후 동일한 방식으로 결과 처리 예정 (_07_fetch | fetch_01.phpt, fetch_array.phpt, fetch_assoc.phpt) (_18_enum | 모든 테스트)
- 그외 EXPECTED FAILED TEST는 odbc_field_type에서 미지원 type 이슈, odbc_field_len에서 길이 값 Function 관련 이슈, type의 의한 memory issue이슈로 ODBC 수정 배포 예정.