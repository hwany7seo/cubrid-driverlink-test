
본 테스트는 pyodbc(https://github.com/mkleehammer/pyodbc) 인터페이스를 통해 cubrid jdbc를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- Python 3.12.2 (https://www.python.org/)
- pyodbc 5.3.0


테스트 방법
python3.12 -m pip install pyodbc (최초 실행)
-Windows
```
run_tests.bat 실행 (기본 테스트)
```

-Linux 

주의 사항 : pyodbc 변환테스트에 경우 local환경에 CUBRID 설치되어 있어야 합니다. 
etc/hosts에 Local IP에 'test-db-server'를 호스트를 추가하세요.
```
./test_linux.sh (기본 테스트)
./test_odbc_linux.sh (cubrid-python unitest를 pyodbc로 변환 테스트)
```

테스트 결과
- 특이사항 없음
