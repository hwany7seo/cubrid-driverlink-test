#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")"

if ! command -v npm >/dev/null 2>&1 || ! command -v node >/dev/null 2>&1; then
  echo "node/npm not found"
  echo "Install nvm, then:"
  echo "curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash"
  echo "source ~/.bashrc"
  echo "nvm install v22.22.1"
  echo "nvm use v22.22.1"
  exit 1
fi

NODE_MAJOR="$(node -p "parseInt(process.versions.node.split('.')[0], 10)")"
if [ "$NODE_MAJOR" -lt 18 ]; then
  echo "ERROR: Node.js $(node -v) is too old. Use Node 18+."
  exit 1
fi

java_native_ok() {
  [ -n "$(find node_modules/java/build -name '*.node' 2>/dev/null | head -1)" ]
}

if [ ! -d node_modules ] || [ ! -d node_modules/chai ] || ! java_native_ok; then
  echo "Installing dependencies..."
  npm install
fi

if ! java_native_ok; then
  echo "ERROR: java (node-java) native addon missing or build failed."
  echo "Try: rm -rf node_modules && npm install"
  echo "Needs: JDK, python3, gcc/make (node-gyp)."
  exit 1
fi

echo "=========================================="
echo "CUBRID nodejs-jdbc (Async/Await) Wrapper Test"
echo "=========================================="

npx mocha 'cubrid-test/*.js' --reporter spec --timeout 60000 --exit

echo ""
echo "=========================================="
echo "Test Summary"
echo "=========================================="
