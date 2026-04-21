해당 Repository의 소스는 아래와 같은 환경에서 테스트 되었습니다.

* CUBRID JDBC 11.3.0.0047
* CUBRID ODBC 11.4.1.0101 

go-odbc, node-jdbc, perl-odbc, php-pdo-odbc, python-odbc, ruby-odbc에 대한 
간단한 연결&Query 테스트와 각 Driver 별 Unit-Test를 각 JDBC, ODBC 연동 라이브러리를 사용하는 코드로 변경하였습니다.

테스트 방법은 각 폴더 별 README 참고하세요.
공통적인 Linux 및 Windows ODBC 설정 방법은 아래와 같습니다.

- Windows
```
1. odbc 관리자 설정 (CUBRID_ANCI, CUBRID_Unicode)
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