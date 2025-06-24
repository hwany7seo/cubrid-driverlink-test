본 테스트는 PHP(https://github.com/php/php-src/) 내부 Module을 통해 cubrid ODBC를 호출하는 형태로 사용됨.
PHP의 내부 Module은 ODBC, PDO는 PDO-ODBC를 사용합니다.

본 테스트는 아래와 같은 환경에서 테스트 됨
- PHP 7.4.2 (https://www.php.net/)
- PHP ODBC Module (https://github.com/php/php-src/tree/master/ext/odbc)
- PHP PDO-ODBC Module (https://github.com/php/php-src/tree/master/ext/pdo_odbc)

환경 설정
1. PHP 설치
2. php.ini 수정 (extension 추가)
(for php_odbc.dll) extension=odbc
(for php_pdo_odbc.dll) extension=php_pdo_odbc.dll

테스트 방법
- odbc 관리자 설정
- connectODBC의 설정에 맞게 connection url 변경 필요.
- PHP 설치 폴더에 맞게 'run_tests.bat' 안에 PHP_PATH 변수 수정.
- 'run_tests.bat' 실행 

파일 정보
- php_pdo_odbc_prepare.php (php_pdo_odbc.dll 사용 예제)
- php_odbc_test_prepare.php (php_odbc.dll 사용 예제)

Known Issue
- prepare 후 execute시에 bind 값이 최초값으로 지속됨.