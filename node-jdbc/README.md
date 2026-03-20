본 테스트는 [nodejs-jdbc](https://github.com/pueteam/nodejs-jdbc) 인터페이스를 통해 cubrid jdbc를 호출하는 형태로 사용됨.

테스트 환경
- Node v22.22.1
- nodejs-jdbc v0.1.5

테스트 방법  
npm install (최초 실행)

- linux
```
test_linux.sh (기본 테스트)
./to-nodejs-jdbc/unit-test-pueteam.sh (CUBRID Unit-test 변환 실행)
```

- Windows
```
- 'run_test.bat' 실행
```