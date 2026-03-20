#!/bin/bash

# Python ODBC Test Runner
echo "Running Python ODBC tests..."

TEST_DIR="./to_pyodbc"

if [ ! -d "$TEST_DIR" ]; then
    echo "Error: Directory $TEST_DIR does not exist."
    exit 1
fi

if [ -d "$TEST_DIR/tests" ]; then
    echo "Running tests in $TEST_DIR/tests..."
    bash $TEST_DIR/tests/runtest.sh
fi

if [ -d "$TEST_DIR/tests2" ]; then
    echo "Running tests in $TEST_DIR/tests2..."
    bash $TEST_DIR/tests2/runtest.sh
fi

if [ -d "$TEST_DIR/tests3" ]; then
    echo "Running tests in $TEST_DIR/tests3..."
    bash $TEST_DIR/tests3/runtest.sh
fi

echo "Python ODBC tests completed."
