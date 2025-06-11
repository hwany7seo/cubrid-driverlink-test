@echo off
setlocal enabledelayedexpansion

echo Checking required Ruby gems...

ruby -v >nul 2>&1
if errorlevel 1 (
    echo Ruby is not installed. Please install Ruby.
    pause
    exit /b 1
) else (
    echo Ruby is installed.
)

goto :main

:checkGem
if "%~1"=="" (
    goto :eof
)
for /f "delims=" %%i in ('gem list -i "%~1"') do set GEM_INSTALLED=%%i
if /I "!GEM_INSTALLED!"=="true" (
    echo Gem %~1 is installed.
) else (
    echo Gem %~1 is not installed. Installing...
    gem install %~1
)
goto :eof

:checkRubyOdbc
for /f "delims=" %%i in ('gem list -i ruby-odbc') do set GEM_RUBY_ODBC=%%i
if /I "!GEM_RUBY_ODBC!"=="true" (
    echo Gem ruby-odbc is installed.
) else (
    echo Gem ruby-odbc is not installed.
    echo Unpacking ruby-odbc gem...
    gem unpack ruby-odbc
    echo.
    echo ****************************************************
    echo Manual steps required.
    echo 1. Open the "ext\odbc.c" file in the unpacked ruby-odbc directory.
    echo    Locate the following code:
    echo      sprintf^(buffer, "Unknown info type %%d for ODBC::Connection.get_info", infoType^);
    echo      set_err^(buffer, 1^);
    echo 2. Modify the code as follows:
    echo      sprintf^(^(char^*^)buffer, "Unknown info type %%d for ODBC::Connection.get_info", infoType^);
    echo      set_err^(^(char^*^)buffer, 1^);
    echo    ^(Additional modifications might be necessary.^)
    echo 3. Afterwards, rebuild and install the gem by running:
    echo      gem build ruby-odbc.gemspec
    echo      gem install ruby-odbc-0.999992.gem
    echo ****************************************************
    pause
    exit /b 1
)
goto :eof


:main
set "GEMS=activerecord dbi inifile dbd-odbc"
for %%G in (%GEMS%) do (
    call :checkGem "%%G"
)

call :checkRubyOdbc

@REM echo.
@REM echo Running Ruby Driver test with %ITERATION% iteration(s)...
@REM ruby performance_tests.rb ruby_driver %ITERATION%
@REM echo.
echo Running ODBC test...
ruby performance_tests.rb
echo.
echo All tests completed.
pause
