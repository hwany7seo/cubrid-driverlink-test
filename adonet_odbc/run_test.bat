@echo off
set PATH=%CD%;%PATH%
set BATCH_FILE_PATH=%CD%

echo Running tests 
dotnet build
powershell -Command "dotnet run"

