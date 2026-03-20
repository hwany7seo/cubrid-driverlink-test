#!/bin/bash

echo "start"

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

result_file="result.txt"
if [ -f "$result_file" ]; then
    rm -f "$result_file"
fi

echo Running ODBC test 
cd to-odbc
for test_file in *.t; do
    echo "Running $test_file..."
    perl "$test_file" >> "$result_file" 2>&1
    echo "-------------------------------------"
done
echo
echo All tests completed.
