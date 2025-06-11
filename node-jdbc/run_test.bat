@echo off
setlocal

set BATCH_FILE_PATH=%CD%

npm run test

echo All tests completed.
endlocal

