#!/bin/bash

echo "=========================================="
echo "CUBRID nodejs-jdbc (Async/Await) Wrapper Test"
echo "=========================================="

cd "$(dirname "$0")"

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Run newly converted tests
echo ""
echo "Running async/await JDBC tests..."
echo ""

npx mocha 'cubrid-test/*.js' --reporter spec --timeout 60000 --exit

echo ""
echo "=========================================="
echo "Test Summary"
echo "=========================================="
