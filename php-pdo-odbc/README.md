본 테스트는 PHP(https://github.com/php/php-src/) 내부 Module을 통해 cubrid ODBC를 호출하는 형태로 사용됨.
PHP의 내부 Module은 ODBC, PDO는 PDO-ODBC를 사용합니다.

본 테스트는 아래와 같은 환경에서 테스트 됨
- PHP 7.4.2 (https://www.php.net/)
- PHP ODBC Module (https://github.com/php/php-src/tree/master/ext/odbc)
- PHP PDO-ODBC Module (https://github.com/php/php-src/tree/master/ext/pdo_odbc)

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

테스트 방법
Windows
```
- odbc 관리자 설정
- connectODBC의 설정에 맞게 connection url 변경 필요.
- PHP 설치 폴더에 맞게 'run_tests.bat' 안에 PHP_PATH 변수 수정.
- 'run_tests.bat' 실행 
```
Linux
- odbc 관리자 설정 (~/.odbc.ini and cat ~/.odbcinst.ini)
- ./tes_linux.sh (basic test - php & pdo)
- ./to_php_odbc/run_php_odbc_tests.sh (php tests)
- ./to_php_odbc/run_php_odbc_tests2.sh (php tests2)
- ./to_pdo_odbc/run_pdo_odbc_tests.sh (pdo tests)
```

파일 정보
- php_pdo_odbc_prepare.php (php_pdo_odbc.dll 사용 예제)
- php_odbc_test_prepare.php (php_odbc.dll 사용 예제)

