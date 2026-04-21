해당 Repository의 소스는 아래와 같은 환경에서 테스트 되었습니다.

* CUBRID JDBC 11.3.0.0047
* CUBRID ODBC 11.4.1.0101 

go-odbc, node-jdbc, perl-odbc, php-pdo-odbc, python-odbc, ruby-odbc에 대한 
테스트는 
1. 간단한 연결&Query 테스트
2. 각 Driver 별 Unit-Test를 각 JDBC, ODBC 연동 라이브러리를 사용도록 코드 변경

간단한 연결 & Query Test는 Windows, Linux에서 테스트 가능하며,
각 Driver 별 변환 테스트는 Linux만 가능합니다. (테스트 코드는 공용이므로 Windows batch파일만 추가하면 테스트가 가능할 것입니다.)
테스트 방법은 각 폴더 별 README 참고하세요.
 
공통적인 Linux 및 Windows ODBC 설정 방법은 아래와 같습니다.
또한 모든 테스트는 hostname을 'test-db-server'를 사용하므로 Connection URL을 DSN가 아닌 Driver를 사용하는 테스트의 경우
Windows와 Linux에 CUBRID가 설치된 IP의 호스트 이름을 'test-db-server'로 등록해주세요.

- Windows
```
1. ODBC 데이터 원본 관리자 (64비트) DSN 추가
 (CUBRID_ANCI, CUBRID_Unicode)

```
- Linux
1. unixODBC 설치
2. odbc 관리자 설정 (~/.odbc.ini and cat ~/.odbcinst.ini) : https://github.com/CUBRID/cubrid-odbc/blob/develop/README.txt
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