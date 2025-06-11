@echo off

if "%1"=="" (
  set ITERATION=1
) else (
  set ITERATION=%1
)

echo Checking required Perl modules...
where cpanm >nul 2>&1
if errorlevel 1 (
    echo cpanm is not installed. Installing cpanminus...
    powershell -Command "iwr -useb https://cpanmin.us | perl -"
) else (
    echo cpanm is installed.
)

set modules=Test::More DBI Time::HiRes JSON threads Config::Simple

for %%M in (%modules%) do (
    perl -M%%M -e "exit" >nul 2>&1
    if errorlevel 1 (
        echo Module %%M is not installed. Installing...
        cpanm %%M
    ) else (
        echo Module %%M is installed.
    )
)

echo Running ODBC test 
perl .\performance_tests.t
echo.
echo All tests completed.
pause
