@echo off

for /f "tokens=*" %%i in ('pip list ^| findstr pyodbc') do set PYODBC_VERSION=%%i

if "%PYODBC_VERSION%" neq "pyodbc  5.2.0" (
    echo "pyodbc 5.2.0 is not installed"
    pip install pyodbc==5.2.0
) else (
    echo "pyodbc 5.2.0 is installed"
)

python odbc.py
