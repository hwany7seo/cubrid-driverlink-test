#!/bin/bash

SCRIPT_DIR=$(dirname $(readlink -f $0))
PYTHON=$(which python3)
TEST_RESULT_DIR=$SCRIPT_DIR/test_result

echo "Python: $PYTHON"
$PYTHON --version > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "python not found"
    exit 0
fi

mkdir -p "$TEST_RESULT_DIR"

for file in $SCRIPT_DIR/test_*.py; do
    if [ -f "$file" ]; then
        base=$(basename "$file")
        out="$TEST_RESULT_DIR/${base}.result"
        echo "Running $file..."
        if command -v pytest >/dev/null 2>&1; then
            pytest "$file" >"$out" 2>&1
        else
            $PYTHON -m pytest "$file" >"$out" 2>&1
        fi
        # cat "$out"
    fi
done