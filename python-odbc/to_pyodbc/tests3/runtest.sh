#!/bin/bash

SCRIPT_DIR=$(dirname $(readlink -f $0))
PYTHON=$(which python3.12)
TEST_RESULT_DIR=$SCRIPT_DIR/test_result

# Parse pytest final summary line from $1 (result file). Prints: failed passed skipped
# (errors are folded into failed)
parse_pytest_counts() {
    local out=$1
    local summary
    summary=$(grep -E '^=+.*(passed|failed|skipped|error)' "$out" | tail -1)
    if [ -z "$summary" ]; then
        echo "0 0 0"
        return 1
    fi
    local f=0 p=0 s=0 e=0
    [[ $summary =~ ([0-9]+)[[:space:]]+failed ]] && f=${BASH_REMATCH[1]}
    [[ $summary =~ ([0-9]+)[[:space:]]+passed ]] && p=${BASH_REMATCH[1]}
    [[ $summary =~ ([0-9]+)[[:space:]]+skipped ]] && s=${BASH_REMATCH[1]}
    [[ $summary =~ ([0-9]+)[[:space:]]+error ]] && e=${BASH_REMATCH[1]}
    echo "$((f + e)) $p $s"
    return 0
}

echo "Python: $PYTHON"
$PYTHON --version > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "python not found"
    exit 0
fi

mkdir -p "$TEST_RESULT_DIR"

ran=0
total_pass=0
total_skip=0
total_fail=0

for file in $SCRIPT_DIR/test_*.py; do
    if [ -f "$file" ]; then
        base=$(basename "$file")
        out="$TEST_RESULT_DIR/${base}.result"
        echo "Running $file..."
        ran=$((ran + 1))
        if command -v pytest >/dev/null 2>&1; then
            pytest "$file" >"$out" 2>&1
        else
            $PYTHON -m pytest "$file" >"$out" 2>&1
        fi
        rc=$?
        counts=$(parse_pytest_counts "$out")
        parsed=$?
        read -r mod_fail mod_pass mod_skip <<< "$counts"
        if [ $parsed -ne 0 ] && [ $rc -ne 0 ]; then
            mod_fail=1
            mod_pass=0
            mod_skip=0
        fi
        total_fail=$((total_fail + mod_fail))
        total_pass=$((total_pass + mod_pass))
        total_skip=$((total_skip + mod_skip))
        echo "  -> pass=$mod_pass skip=$mod_skip fail=$mod_fail"
    fi
done

echo ""
if [ $ran -eq 0 ]; then
    echo "No test_*.py modules found under $SCRIPT_DIR"
    exit 0
fi

echo "Total: pass=$total_pass skip=$total_skip fail=$total_fail"

if [ $total_fail -gt 0 ]; then
    exit 1
fi
exit 0
