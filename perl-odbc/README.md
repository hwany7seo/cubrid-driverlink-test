# Perl Database Interface
## 테스트는 DBI(with DBD-ODBC) 인터페이스를 통해 cubrid odbc를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- Strawberry Perl: 5.40.0
- DBD-ODBC: 1.43 (https://github.com/perl5-dbi/DBD-ODBC/releases/tag/DBD-ODBC-1.43)
- DBI: 1.647 (https://github.com/perl5-dbi/dbi/releases/tag/1.647)


### Windows
#### 테스트 실행
- run_tests.bat 실행 (환경설정 포함)

### Linux (Rocky 8.10)
#### 사전 설치
```
sudo dnf reinstall gcc-toolset-9*
sudo dnf install perl-App-cpanminus openssl-devel perl-LWP-Protocol-https
```

#### DBI 설치 실패 시 
```
/opt/rh/gcc-toolset-9/root/usr/lib/gcc/x86_64-redhat-linux/9/plugin 폴더에
 annobin.so.0.0.0파일을 gcc-annobin.so 심볼릭 링크파일을 생성해야합니다.
 ```

#### 테스트 실행
```
$> sh test_linux.sh
```

## 결과
### 미지원
- last_insert_id (ODBC 미지원)
### 확인 필요
- bind_enum_apis-341.t에서 열거형 bind_param시 INDEX를 지원 관련.
