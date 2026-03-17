#!/bin/bash

# Python ODBC Test Runner
echo "Running Python ODBC tests..."

TEST_DIR="./to_pyodbc"

if [ ! -d "$TEST_DIR" ]; then
    echo "Error: Directory $TEST_DIR does not exist."
    exit 1
fi

# Run tests in tests/
if [ -d "$TEST_DIR/tests" ]; then
    echo "Running tests in $TEST_DIR/tests..."
    for file in "$TEST_DIR/tests"/*.py; do
        if [ -f "$file" ]; then
            echo "Running $file..."
            # Change directory to test folder to handle relative paths (e.g. python_config.xml)
            (cd "$TEST_DIR/tests" && python3 "$(basename "$file")" > "$(basename "$file").result" 2>&1)
            cat "$file.result"
        fi
    done
fi

# Run tests in tests2/
if [ -d "$TEST_DIR/tests2" ]; then
    echo "Running tests in $TEST_DIR/tests2..."
    for file in "$TEST_DIR/tests2"/*.py; do
        if [ -f "$file" ]; then
            echo "Running $file..."
            (cd "$TEST_DIR/tests2" && python3 "$(basename "$file")" > "$(basename "$file").result" 2>&1)
            cat "$file.result"
        fi
    done
fi

# Run tests in tests3/
if [ -d "$TEST_DIR/tests3" ]; then
    echo "Running tests in $TEST_DIR/tests3..."
    for file in "$TEST_DIR/tests3"/*.py; do
        if [ -f "$file" ]; then
            echo "Running $file..."
            (cd "$TEST_DIR/tests3" && python3 "$(basename "$file")" > "$(basename "$file").result" 2>&1)
            cat "$file.result"
        fi
    done
fi

echo "Python ODBC tests completed."
