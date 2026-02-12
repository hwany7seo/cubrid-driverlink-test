#! /bin/bash

shell_path=$(dirname $0)

cd $shell_path

if [ ! -d "node_modules" ]; then
    npm install
fi

npm run test