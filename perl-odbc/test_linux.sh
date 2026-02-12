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
    sudo dnf install perl-App-cpanminus
else
    echo cpanm is installed.
fi

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
perl performance_tests.t
echo
echo All tests completed.

