본 테스트는 [go odbc](https://github.com/alexbrainman/odbc) 인터페이스를 통해 cubrid ODBC를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- go 1.24.1 (https://go.dev/)

테스트 방법
- go 설치
- odbc 관리자 설정
- connectODBC의 설정에 맞게 connection url 변경 필요.
- 'go mod tidy' command 실행
- 'run_test.bat' 배치 파일 실행


Known Issue
- prepare 후 execute시에 bind 값이 최초값으로 지속됨.