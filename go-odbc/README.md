본 테스트는 [go odbc](https://github.com/alexbrainman/odbc) 인터페이스를 통해 cubrid ODBC를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- go 1.24.1 (https://go.dev/)

사전 설치 방법
- go 설치
- odbc 관리자 설정
- connectODBC의 설정에 맞게 connection url 변경 필요.
- 'go mod tidy' command 실행

Windows
- run_test.bat 실행

Linux
- 기본 테스트
  - test_linux.sh
- CUBRID-go Sample Unitest 
  - unit_test.sh

테스트 케이스 변환 검증 Known Issue 
- SET Type insert 되지 않는 문제 (example.go)
