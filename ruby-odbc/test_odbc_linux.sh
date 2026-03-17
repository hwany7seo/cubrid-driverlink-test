#!/bin/bash

# Ruby ODBC Test Runner
echo "Running Ruby ODBC tests..."

TEST_DIR="./to_odbc"

if [ ! -d "$TEST_DIR" ]; then
    echo "Error: Directory $TEST_DIR does not exist."
    exit 1
fi

# Run all test files
for file in "$TEST_DIR"/*.rb; do
    if [ -f "$file" ]; then
        echo "Running $file..."
        ruby "$file"
    fi
done

echo "Ruby ODBC tests completed."
