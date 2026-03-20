#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")"

NODE_MAJOR="$(node -p "parseInt(process.versions.node.split('.')[0], 10)" 2>/dev/null || echo 0)"
if [ "$NODE_MAJOR" -lt 18 ]; then
    echo "Use Node 18 or newer. Current: $(node -v 2>/dev/null || echo unknown)"
    exit 1
fi

if [ ! -d node_modules ] || [ -z "$(find node_modules/java/build -name '*.node' 2>/dev/null | head -1)" ]; then
    npm install
fi

npm run test