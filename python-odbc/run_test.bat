@echo off

set PYTHON36_PATH=C:\Python\Python36
set PYTHON310_PATH=C:\Python\Python310
set PYTHON311_PATH=C:\Python\Python311
set PYTHON312_PATH=C:\Python\Python312
set USE_PYTHON_PATH=%PYTHON312_PATH%
set PYTHON_PATH=%USE_PYTHON_PATH%\python.exe
set PIP_PATH=%USE_PYTHON_PATH%\Scripts\pip.exe

for /f "tokens=*" %%i in ('%PIP_PATH% list ^| findstr pyodbc') do set PYODBC_VERSION=%%i

if "%PYODBC_VERSION%" neq "pyodbc  5.2.0" (
    echo "pyodbc 5.2.0 is not installed"
    %PIP_PATH% install pyodbc==5.2.0
) else (
    echo "pyodbc 5.2.0 is installed"
)

%PYTHON_PATH% odbc.py
