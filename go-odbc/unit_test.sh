#! /bin/bash
SHELL_PATH=$(pwd)
GO_GET_PATH=$(go env GOPATH)
echo "GO_GET_PATH: $GO_GET_PATH"
go run to_odbc/bind_test.go
go run to_odbc/example.go
go run to_odbc/olympic.go
