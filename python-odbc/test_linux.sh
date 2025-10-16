#! /bin/bash

PYTHON_VERSION=3.12
PYTHON_VERSION_MINOR=$(echo $PYTHON_VERSION | cut -d '.' -f 2)
PYTHON_PATH=$(which python$PYTHON_VERSION)
PIP_PATH=$(which pip$PYTHON_VERSION)

$PYTHON_PATH --version > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "python not found"
    exit 0
fi

PYTHON_INSTALLED_LIST=$($PIP_PATH list | grep pyodbc)

if [[ $PYTHON_INSTALLED_LIST == *"pyodbc"* ]]; then
    echo "pyodbc already installed" > /dev/null 2>&1
else 
    echo "pyodbc not installed"
    $PYTHON_PATH -m $PIP_PATH install pyodbc
fi

$PYTHON_PATH odbc_anci.py
#$PYTHON_PATH odbc.py
