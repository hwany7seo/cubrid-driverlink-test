#!/bin/bash

echo "start"

SCRIPT_DIR=$(dirname $(readlink -f "$0"))
RESULT_FILE="$SCRIPT_DIR/result.txt"
modules="LWP::Protocol::https Test::More DBI DBD::ODBC Time::HiRes JSON threads Config::Simple"
ITERATION=$1

if [ -z "$ITERATION" ]; then
  ITERATION=1
fi

echo Checking required Perl modules...
if ! which cpanm &> /dev/null; then
    echo cpanm is not installed. Installing cpanminus...
    if command -v dnf &> /dev/null; then
        sudo dnf install -y perl-App-cpanminus
    elif command -v yum &> /dev/null; then
        sudo yum install -y perl-App-cpanminus
    fi
else
    echo cpanm is installed.
fi

if ! perl -MLWP::Protocol::https -e "exit" &> /dev/null; then
    echo "Bootstrapping HTTPS for cpanm (perl-LWP-Protocol-https)..."
    if command -v dnf &> /dev/null; then
        sudo dnf install -y perl-LWP-Protocol-https perl-Net-SSLeay openssl-devel
    elif command -v yum &> /dev/null; then
        sudo yum install -y perl-LWP-Protocol-https perl-Net-SSLeay openssl-devel
    fi
fi

RPM_PERL_PKGS="perl-DBI perl-DBD-ODBC perl-JSON perl-Config-Simple"
for package in $RPM_PERL_PKGS; do
    if ! dnf list installed | grep -q "$package"; then
        echo "Installing $package..."
        sudo dnf install -y $package
    else
        echo "$package is already installed."
    fi
done


for module in $modules; do
    echo -n "Checking $module... "
    if ! perl -M$module -e "exit" &> /dev/null; then
        echo "Not installed. Installing $module..."
        sudo cpanm $module
        if [ $? -eq 0 ]; then
            echo "$module installed successfully."
        else
            echo "Failed to install $module. Please check the error messages above."
        fi
    else
        echo "Installed."
    fi
    echo "-------------------------------------"
done

echo Running ODBC test 
rm -f "$RESULT_FILE"
cd t

FAILED_TESTS=()
TOTAL=0
PASSED=0

for test_file in *.t; do
    TOTAL=$((TOTAL + 1))
    echo "Running $test_file..."
    echo "-------------- $test_file -------------- $test_file" >> "$RESULT_FILE"

    TEST_OUT=$(mktemp)
    perl "$test_file" > "$TEST_OUT" 2>&1
    TEST_EXIT=$?
    cat "$TEST_OUT" >> "$RESULT_FILE"

    if [ "$TEST_EXIT" -ne 0 ] || grep -aqE '^not ok|Looks like you failed|Dubious|Failed test' "$TEST_OUT"; then
        FAILED_TESTS+=("$test_file")
        echo "  => FAILED (exit=$TEST_EXIT)"
        grep -aE '^not ok|Looks like you failed|#   Failed test' "$TEST_OUT" || true
    else
        PASSED=$((PASSED + 1))
        echo "  => passed"
    fi

    rm -f "$TEST_OUT"
    echo "-------------------------------------"
done

echo
echo "========== Test Summary =========="
echo "Total:  $TOTAL"
echo "Passed: $PASSED"
echo "Failed: ${#FAILED_TESTS[@]}"

if [ "${#FAILED_TESTS[@]}" -gt 0 ]; then
    echo
    echo "Failed test list:"
    for f in "${FAILED_TESTS[@]}"; do
        echo "  - $f"
    done
    echo
    echo "See details in: $RESULT_FILE"
    echo "All tests completed with failures."
    exit 1
fi

echo "All tests passed."
exit 0
