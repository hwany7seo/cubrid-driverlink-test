@echo off
setlocal

set "PHP_PATH=C:\php_pdo\pdo\php-7.4.2-Win32-vc15-x64\php.exe"
@REM set "PHP_PATH=C:\php_pdo\pdo\php-7.4.2-nts-Win32-vc15-x64\php.exe"
@REM set "PHP_PATH=C:\php_pdo\pdo\php-8.2.28-nts-Win32-vs16-x64\php.exe"
@REM set "PHP_PATH=C:\php_pdo\pdo\php-8.2.28-Win32-vs16-x64\php.exe"

REM Run PHP tests
echo Running PHP tests...
call %PHP_PATH% php_odbc_test_prepare.php
echo End PHP tests...
echo ------------------------------------------------------------
echo ------------------------------------------------------------
echo ------------------------------------------------------------

@REM REM Run PDO ODBC tests
@REM echo Running PDO ODBC tests...
@REM call %PHP_PATH% .\php_pdo_odbc_prepare.php

@REM REM Run mysql PHP tests
@REM echo Running mysql PHP tests...
@REM call %PHP_PATH% php_odbc_test_prepare_mysql.php

echo All tests completed.
endlocal
pause
