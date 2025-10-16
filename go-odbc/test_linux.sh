#! /bin/bash

go version > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "go not found"
    exit 0
fi

go run cubrid-odbc.go