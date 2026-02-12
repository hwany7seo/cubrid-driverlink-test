본 테스트는 DBI(with DBD-ODBC) 인터페이스를 통해 cubrid jdbc를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- Strawberry Perl: 5.40.0
- DBD-ODBC: 1.43 (https://github.com/perl5-dbi/DBD-ODBC/releases/tag/DBD-ODBC-1.43)
- DBI: 1.647 (https://github.com/perl5-dbi/dbi/releases/tag/1.647)

테스트 방법
- run_tests.bat 실행 (환경설정 포함)

Known Issue
- prepare 후 execute시에 bind 값이 최초값으로 지속됨.



Linux

sudo dnf reinstall gcc-toolset-9*
sudo dnf install perl-App-cpanminus openssl-devel perl-LWP-Protocol-https


DBI 설치 실패 시 
/opt/rh/gcc-toolset-9/root/usr/lib/gcc/x86_64-redhat-linux/9/plugin 폴더에
 annobin.so.0.0.0파일을 gcc-annobin.so 심볼릭 링크파일을 생성해야합니다.