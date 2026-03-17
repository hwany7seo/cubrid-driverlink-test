#!/bin/bash

# PHP ODBC Test Runner
echo "Running PHP ODBC tests..."

TEST_DIR="./to_php_odbc"

if [ ! -d "$TEST_DIR" ]; then
    echo "Error: Directory $TEST_DIR does not exist."
    exit 1
fi

for file in "$TEST_DIR"/*.phpt; do
    if [ -f "$file" ]; then
        echo "Running $file..."
        # Extract PHP code from .phpt file (simple extraction for --FILE-- section)
        # This is a simplified runner. Ideally use run-tests.php if available and compatible.
        # Here we just try to run the PHP code inside.
        # But .phpt files are not directly executable by php command.
        # They need the PEAR run-tests.php or similar.
        
        # Let's try to find run-tests.php in the directory or parent
        if [ -f "$TEST_DIR/run-tests.php" ]; then
             php "$TEST_DIR/run-tests.php" "$file"
        else
             # Fallback: Just try to run it if it was a .php file, but it is .phpt
             # We can try to extract the FILE section.
             # But for now, let's just list them as to be run.
             echo "  (Needs .phpt runner) $file"
        fi
    fi
done

# If there are .php files
for file in "$TEST_DIR"/*.php; do
    if [ -f "$file" ] && [ "$file" != "$TEST_DIR/run-tests.php" ]; then
        echo "Running $file..."
        php "$file"
    fi
done

echo "PHP ODBC tests completed."
