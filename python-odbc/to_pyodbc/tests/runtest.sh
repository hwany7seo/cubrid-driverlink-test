#!/bin/bash

SCRIPT_DIR=$(dirname $(readlink -f "$0"))
cd "$SCRIPT_DIR" || exit 1

PYTHON=$(which python3.12)
TEST_RESULT_DIR=$SCRIPT_DIR/test_result

mkdir -p $TEST_RESULT_DIR

$PYTHON -m pytest $SCRIPT_DIR/test_*.py > $TEST_RESULT_DIR/test.result 2>&1
cat $TEST_RESULT_DIR/test.result

if [ $? -ne 0 ]; then
    echo "Test failed"
    exit 1
fi

exit 0