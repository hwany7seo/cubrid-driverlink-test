@echo off
set PATH=%CD%;%PATH%
set BATCH_FILE_PATH=%CD%

echo Running tests 
dotnet build
powershell -Command "dotnet run | Tee-Object -FilePath '%BATCH_FILE_PATH%\results_go_adonet_test_prepare_%%c.txt'"

