본 테스트는 [Ruby ODBC](https://github.com/larskanis/ruby-odbc) 인터페이스를 통해 cubrid jdbc를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- ruby 3.3.7-1 (https://www.ruby-lang.org/ko/downloads/)
- ruby-odbc (https://github.com/larskanis/ruby-odbc)


ruby-odbc 설치 관련
Windows의 Unicode 관련 타입(SQLWCHAR)과 일반 C 문자열(char*) 사이의 불일치로 컴파일 오류가 발생해 코드 수정 필요
```
gem unpack ruby-odbc

======================================================
# ruby-odbc-0.999992/ext/odbc.c 수정 (LINE 4075~4076)
sprintf((char*)buffer, "Unknown info type %d for ODBC::Connection.get_info", infoType);
set_err((char*)buffer, 1);
======================================================

gem build ruby-odbc.gemspec
gem install ruby-odbc-0.999992.gem
```

테스트 방법
- run_tests.bat 실행 (필수 lib 설치 및 실행)

예제는 DBI를 사용하여 ruby-odbc와 DBI(with DBD:ODBC)를 사용하는 방식 두가지로
작성되어 있습니다.