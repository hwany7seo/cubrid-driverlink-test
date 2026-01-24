#! /bin/bash
SHELL_PATH=$(pwd)
GO_GET_PATH=$(go env GOPATH)
echo "GO_GET_PATH: $GO_GET_PATH"
go run cubrid-test/bind_test.go
go run cubrid-test/example.go
go run cubrid-test/olympic.go







