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

#### cpanm / XS 빌드 시 annobin 플러그인 오류가 날 때
`gcc-toolset-9-annobin` 패키지는 플러그인 파일명이 **`annobin.so`** 이지 `gcc-annobin.so` 가 아닙니다.  
시스템 GCC 8용 `gcc-annobin.so`로 링크하면 *“plugin built for 8.x but run with 9.x”* 가 납니다.

**같은 `9/plugin` 디렉터리 안에서** 아래처럼 연결합니다 (`annobin.so` → `gcc-annobin.so`).

```bash
sudo ln -sf annobin.so /opt/rh/gcc-toolset-9/root/usr/lib/gcc/x86_64-redhat-linux/9/plugin/gcc-annobin.so
```

이미 잘못된 대상(예: `/usr/lib/gcc/.../8/plugin/...`)으로 링크돼 있으면 위 경로의 `gcc-annobin.so`를 지운 뒤 다시 실행합니다.

#### 테스트 실행
```
$> sh test_linux.sh
```

## 결과
### 미지원
- last_insert_id (ODBC 미지원)
### 확인 필요
- bind_enum_apis-341.t에서 열거형 bind_param시 INDEX를 지원 관련.
