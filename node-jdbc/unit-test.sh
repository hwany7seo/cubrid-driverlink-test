#!/bin/bash

echo "=========================================="
echo "CUBRID node-cubrid vs JDBC Comparison Test"
echo "=========================================="

cd "$(dirname "$0")"

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Run all converted tests
echo ""
echo "Running all JDBC compatibility tests..."
echo ""

npx mocha 'cubrid-test/*.js' --reporter spec --timeout 10000

echo ""
echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo ""
echo "Tests completed. Check the output above for:"
echo "  ✓ Passing tests (supported features)"
echo "  - Skipped tests (not supported in JDBC)"
echo "  ✗ Failing tests (implementation issues)"
echo ""
