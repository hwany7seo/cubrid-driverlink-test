<?php
/**
 * ext/odbc + cubrid_odbc_compat.inc 로 대체할 수 없는 CUBRID PHP 확장 전용 API를 쓰는 테스트용 SKIPIF.
 * SKIPIF 블록에서 require_once('skipif.inc'); 다음에 이 파일을 로드하면 즉시 skip 한다.
 * BLOB/CLOB ODBC 왕복 검증은 cubrid_odbc_lob_basic.phpt 를 사용한다.
 */
die('skip PHP ODBC(ext/odbc): CUBRID PHP Extension-Only API — Not supported by PHP ODBC');
