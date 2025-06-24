본 테스트는 [node-jdbc](https://github.com/CraZySacX/node-jdbc) 인터페이스를 통해 cubrid jdbc를 호출하는 형태로 사용됨.

본 테스트는 아래와 같은 환경에서 테스트 됨
- node v18.20.8 (https://nodejs.org/en)

테스트 방법
- node.js 및 npm 설치
- lib 폴더에 jdbc 복사 (파일명 변경시 package.json, test/cubrid-jdbc.test.js내 파일명 변경 필요).
- npm install (최초 실행)
- 'run_test.bat' 배치 파일 실행