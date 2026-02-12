# node-cubrid vs JDBC 제한사항 요약 (테스트 완료)

## 🎯 핵심 요약
**JDBC를 사용할 때 node-cubrid 대비 사용할 수 없는 기능 20개**

### 테스트 실행 결과
- ✅ **통과**: 62개 테스트
- ⊗ **미지원 (Skipped)**: 20개 테스트
- ❌ **실패**: 2개 테스트 (node-jdbc 라이브러리 버그)

---

## ❌ JDBC에서 완전 미지원 기능 목록

### 연결 관리 (4개)
1. ❌ `setConnectionTimeout()` - URL에서만 설정 가능
2. ❌ `getActiveHost()` - 활성 호스트 정보 조회 불가
3. ❌ 연결 이벤트 (`on('connect')`, `on('error')`)
4. ❌ `_socket` 속성 접근 불가

### CUBRID 특화 기능 (6개)
5. ❌ `getEngineVersion()` - CUBRID 엔진 버전 조회 불가
6. ❌ `setEnforceOldQueryProtocol()` - 프로토콜 선택 불가
7. ❌ `brokerInfo` - 브로커 정보 접근 불가
8. ❌ `getSchema()` - CUBRID 방식 스키마 조회 불가
9. ❌ `getDatabaseParameter()` - DB 파라미터 조회 불가
10. ❌ `setDatabaseParameter()` - DB 파라미터 설정 불가

### 쿼리 관리 (6개)
11. ❌ Query Handle 기반 관리
12. ❌ `_queryResultSets` 자동 관리
13. ❌ `closeQuery(handle)` - ResultSet.close() 사용 필요
14. ❌ `queryAll()` - 전체 결과 자동 fetch 불가
15. ❌ `fetch()` - 커서 기반 fetch 불가
16. ❌ `executeWithTypedParams()` - 타입 지정 파라미터 실행 불가

### LOB 처리 (2개)
17. ❌ `lobRead()` - LOB 읽기 전용 메서드 없음
18. ❌ `lobWrite()` - LOB 쓰기 전용 메서드 없음

### 배치 작업 (2개)
19. ❌ `batchExecuteNoQuery()` - 간편 배치 실행 불가
20. ❌ `addBatch()` - node-jdbc 라이브러리 미구현

### 기타 (1개)
21. ⚠️ `getAutoCommitMode()` - 동기 방식 대신 비동기 콜백 필요

---

## 🐛 node-jdbc 라이브러리 버그

### 1. Connection Pool 오류
```javascript
// 에러 발생
Error: No more pool connections available
```
- maxpoolsize 설정에도 불구하고 다중 연결 실패

### 2. Batch API 미구현
```javascript
// 에러 발생
statement.addBatch(sql, callback); // Error: NOT IMPLEMENTED
```
- JDBC API 중 addBatch/executeBatch 미지원

---

## ✅ JDBC에서 정상 동작하는 기능 (6개)

1. ✅ 기본 쿼리 실행 (`SELECT`, `INSERT`, `UPDATE`, `DELETE`)
2. ✅ PreparedStatement (파라미터화된 쿼리)
3. ✅ 트랜잭션 커밋 (`commit()`)
4. ✅ 트랜잭션 롤백 (`rollback()`)
5. ✅ 날짜/시간 데이터 타입
6. ✅ ENUM 데이터 타입

---

## 💡 결론

### JDBC 사용 권장
- ✅ 기본 CRUD만 사용
- ✅ 표준 SQL만 사용
- ✅ CUBRID 특화 기능 불필요

### node-cubrid 사용 필수
- ❌ CUBRID 고급 기능 필요
- ❌ 프로토콜/브로커 제어 필요
- ❌ LOB 데이터 처리 빈번
- ❌ 스키마 정보 상세 조회 필요
- ❌ 이벤트 기반 연결 관리 필요

### 지원율
- **JDBC 지원**: 21% (6개 / 28개)
- **미지원**: 75% (21개 / 28개)
- **부분 지원**: 4% (1개 / 28개)

---

## 📝 테스트 실행 방법

```bash
cd /home/hwanyseo/source/fork/test/cubrid-driverlink-test/node-jdbc
./unit-test.sh
```

상세 리포트: `JDBC_COMPATIBILITY_REPORT.md` 참조
