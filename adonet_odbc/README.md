본 테스트는 .NET 프레임워크 내부의 System.Data.Odbc 표준 인터페이스를 통해 cubrid ODBC를 호출하는 형태로 사용됨.
ODBC 문서: https://learn.microsoft.com/ko-kr/cpp/data/odbc/odbc-basics?view=msvc-170

본 테스트는 아래와 같은 환경에서 테스트 됨
- .NET Framework 4.6.1
.net 변경시 adonet_test.csproj에 변경 필요.

테스트 방법
- cubrid odbc 설치
- odbc 관리자 설정
- connectODBC의 설정에 맞게 connection url 변경 필요.
- 'run_test.bat' 배치 파일 실행


