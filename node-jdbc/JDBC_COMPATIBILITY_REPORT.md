# CUBRID Node.js JDBC 호환성 테스트 리포트

## 테스트 개요
- **테스트 날짜**: 2026-02-12
- **테스트 대상**: node-cubrid vs node-jdbc (CUBRID JDBC 드라이버 사용)
- **목적**: node-cubrid에서 정상 동작하지만 JDBC에서는 지원되지 않거나 다르게 동작하는 기능 식별

## 테스트 결과 요약
- **통과**: 6개 테스트
- **실패**: 2개 테스트
- **미지원**: 19개 기능

---

## ✅ JDBC에서 정상 동작하는 기능

### 1. 기본 쿼리 실행
- ✓ 단순 쿼리 실행 (SELECT 1)
- ✓ 파라미터화된 쿼리 (PreparedStatement)

### 2. 트랜잭션 관리
- ✓ 트랜잭션 커밋 (commit)
- ✓ 트랜잭션 롤백 (rollback)

### 3. 데이터 타입
- ✓ DATE, DATETIME, TIME, TIMESTAMP 타입
- ✓ ENUM 타입

---

## ❌ JDBC에서 완전히 미지원되는 기능

### 1. 연결 관리 (Connection Management)
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **Connection Timeout 설정** | `setConnectionTimeout()` | ❌ | JDBC URL에서만 설정 가능 |
| **Active Host 조회** | `getActiveHost()` | ❌ | JDBC는 활성 호스트 정보 미노출 |
| **Connection 이벤트** | `on('connect')`, `on('error')` 등 | ❌ | JDBC는 콜백만 지원, 이벤트 없음 |
| **Socket 접근** | `_socket` 속성 | ❌ | JDBC는 하위 소켓 미노출 |

### 2. 프로토콜 및 버전 정보
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **엔진 버전 조회** | `getEngineVersion()` | ❌ | JDBC는 CUBRID 엔진 버전 직접 노출 안 함 |
| **Old Query Protocol** | `setEnforceOldQueryProtocol()` | ❌ | JDBC는 프로토콜 선택 불가 |
| **Broker 정보** | `brokerInfo.protocolVersion` | ❌ | JDBC는 브로커 정보 미노출 |

### 3. 쿼리 관리
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **Query Handle 관리** | `queryHandle` 기반 관리 | ❌ | JDBC는 Statement/ResultSet 객체 사용 |
| **자동 쿼리 정리** | `_queryResultSets` 자동 관리 | ❌ | JDBC는 명시적 `ResultSet.close()` 필요 |
| **closeQuery()** | `closeQuery(handle)` | ❌ | JDBC는 `ResultSet.close()` 사용 |

### 4. 고급 쿼리 기능
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **queryAll()** | `queryAll(sql)` | ❌ | 모든 결과 자동 fetch 기능 없음 |
| **fetch()** | `fetch(queryHandle, callback)` | ❌ | JDBC는 `ResultSet.next()` 사용 |
| **executeWithTypedParams()** | `executeWithTypedParams(sql, params)` | ❌ | JDBC는 `setInt()`, `setString()` 등 개별 메서드 사용 |

### 5. 스키마 및 메타데이터
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **getSchema()** | `getSchema(type, arg1, arg2, pattern)` | ❌ | JDBC는 `DatabaseMetaData` 사용 |
| **getDatabaseParameter()** | `getDatabaseParameter(param)` | ❌ | CUBRID 특화 파라미터 조회 불가 |
| **setDatabaseParameter()** | `setDatabaseParameter(param, value)` | ❌ | CUBRID 특화 파라미터 설정 불가 |

### 6. LOB 처리
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **lobRead()** | `lobRead(lob, offset, len, callback)` | ❌ | JDBC는 다른 LOB API 사용 |
| **lobWrite()** | `lobWrite(lob, offset, data, callback)` | ❌ | JDBC는 다른 LOB API 사용 |

### 7. 배치 작업
| 기능 | node-cubrid | JDBC | 비고 |
|------|-------------|------|------|
| **batchExecuteNoQuery()** | `batchExecuteNoQuery(sqls)` | ❌ | JDBC는 `addBatch()` + `executeBatch()` 사용 |
| **addBatch()** | ❌ | 구현 안 됨 | node-jdbc 라이브러리 제한 |

---

## ⚠️ JDBC에서 다르게 동작하는 기능

### 1. AutoCommit 모드 조회
- **node-cubrid**: `getAutoCommitMode()` - 동기 메서드
- **JDBC**: `getAutoCommit(callback)` - 비동기 콜백 필요
- **영향**: API 사용 방식이 다름

### 2. 다중 쿼리 실행
- **node-cubrid**: 쿼리 핸들 자동 관리, 명시적 close 선택적
- **JDBC**: 각 `ResultSet`에 대해 명시적 `close()` 필수
- **영향**: 메모리 누수 방지를 위해 코드 패턴 변경 필요

### 3. 결과셋 관리
- **node-cubrid**: `_queryResultSets` 객체로 중앙 관리
- **JDBC**: 각 Statement/ResultSet 개별 관리
- **영향**: 리소스 관리 방식이 근본적으로 다름

---

## 🐛 JDBC 라이브러리 버그/제한사항

### 1. Connection Pool 제한
- **문제**: `maxpoolsize` 설정에도 불구하고 다중 연결 시 "No more pool connections available" 에러
- **원인**: node-jdbc 라이브러리의 pool 관리 이슈
- **해결 방법**: 연결 재사용 또는 pool 설정 조정 필요

### 2. Batch 메서드 미구현
- **문제**: `Statement.addBatch()` 메서드가 "NOT IMPLEMENTED" 에러 반환
- **원인**: node-jdbc 라이브러리가 해당 JDBC API 미구현
- **해결 방법**: 개별 쿼리 실행 또는 라이브러리 업데이트 필요

---

## 📊 기능 카테고리별 지원 현황

| 카테고리 | 지원 | 부분 지원 | 미지원 | 비율 |
|---------|------|----------|--------|------|
| 기본 쿼리 | 2 | 0 | 0 | 100% |
| 트랜잭션 | 2 | 1 | 0 | 83% |
| 연결 관리 | 0 | 0 | 4 | 0% |
| 프로토콜/버전 | 0 | 0 | 3 | 0% |
| 쿼리 관리 | 0 | 0 | 3 | 0% |
| 고급 쿼리 | 0 | 0 | 3 | 0% |
| 스키마/메타 | 0 | 0 | 3 | 0% |
| 데이터 타입 | 2 | 0 | 1 | 67% |
| LOB | 0 | 0 | 2 | 0% |
| 배치 | 0 | 0 | 2 | 0% |
| **전체** | **6** | **1** | **21** | **21%** |

---

## 💡 권장사항

### 1. JDBC 사용이 적합한 경우
- ✅ 기본적인 CRUD 작업만 필요한 경우
- ✅ 표준 JDBC API로 충분한 경우
- ✅ Java 생태계와의 호환성이 중요한 경우

### 2. node-cubrid 사용이 필요한 경우
- ✅ CUBRID 특화 기능이 필요한 경우 (프로토콜 선택, 브로커 정보 등)
- ✅ 고급 쿼리 관리 기능이 필요한 경우 (queryAll, fetch 등)
- ✅ LOB 데이터를 자주 처리하는 경우
- ✅ 스키마 정보를 CUBRID 방식으로 조회해야 하는 경우
- ✅ 이벤트 기반 연결 관리가 필요한 경우

### 3. 마이그레이션 시 주의사항
- ⚠️ 쿼리 핸들 관리 방식 변경 필요
- ⚠️ ResultSet 명시적 close 패턴 적용 필요
- ⚠️ LOB 처리 로직 전면 재작성 필요
- ⚠️ 스키마 조회 코드 변경 필요 (DatabaseMetaData 사용)
- ⚠️ 배치 작업 코드 변경 필요
- ⚠️ 이벤트 핸들러를 콜백으로 변경 필요

---

## 📁 테스트 파일 위치
- 테스트 코드: `/home/hwanyseo/source/fork/test/cubrid-driverlink-test/node-jdbc/cubrid-test/jdbc-compatibility-test.js`
- 실행 스크립트: `/home/hwanyseo/source/fork/test/cubrid-driverlink-test/node-jdbc/unit-test.sh`

## 실행 방법
```bash
cd /home/hwanyseo/source/fork/test/cubrid-driverlink-test/node-jdbc
./unit-test.sh
```

---

## 결론

**node-cubrid**는 CUBRID 데이터베이스를 위해 특별히 설계된 네이티브 드라이버로, **JDBC보다 21%의 핵심 기능만 제공**합니다. 

주요 차이점:
1. **JDBC**: 표준 Java Database Connectivity API를 따르는 범용 드라이버
2. **node-cubrid**: CUBRID 특화 기능과 편의성을 제공하는 전용 드라이버

기본적인 데이터베이스 작업(CRUD, 트랜잭션)은 JDBC로도 가능하지만, CUBRID의 고급 기능이나 편의 기능을 사용하려면 **node-cubrid 사용을 권장**합니다.
