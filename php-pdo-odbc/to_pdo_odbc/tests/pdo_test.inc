<?php
# PDO test framework utilities

class PDOTest {
	// create an instance of the PDO driver, based on
	// the current environment
	//
	// PDO_ODBC_TEST_DSN: 전체 DSN (최우선). 예: odbc:DSN=CUBRID_Unicode
	// PDO_ODBC_TEST_DSN_NAME: ~/.odbc.ini 의 섹션 이름 (기본: CUBRID_Unicode → Driver=CUBRID_ODBC_Unicode)
	// Driver={...} 직접 연결은 odbcinst.ini 경로가 잡힌 뒤에만 동작하므로, DSN 방식을 기본으로 둔다.
	static function dsn_default() {
		$override = getenv('PDO_ODBC_TEST_DSN');
		if ($override !== false && $override !== '') {
			return $override;
		}
		$name = getenv('PDO_ODBC_TEST_DSN_NAME');
		if ($name === false || $name === '') {
			$name = 'CUBRID_Unicode';
		}
		return 'odbc:DSN=' . $name;
	}

	// user_factory DSN (PDO_ODBC_TEST_USER_DSN, else same rules as dsn_default)
	static function dsn_user_default() {
		$override = getenv('PDO_ODBC_TEST_USER_DSN');
		if ($override !== false && $override !== '') {
			return $override;
		}
		return self::dsn_default();
	}

	static function factory($classname = 'PDO', $drop_test_tables = true) {
		$dsn = self::dsn_default();
		$user = 'dba';
		$pass = '';
		$attr = NULL;
                
		if (is_string($attr) && strlen($attr)) {
			$attr = unserialize($attr);
		} else {
			$attr = null;
		}

		if ($user === false) $user = NULL;
		if ($pass === false) $pass = NULL;

		$db = new $classname($dsn, $user, $pass, $attr);

		if (!$db) {
			die("Could not create PDO object (DSN=$dsn, user=$user)\n");
		}

		// clean up any crufty test tables we might have left behind
		// on a previous run
		static $test_tables = array(
			'cubrid_test',
			'cubrid_test2',
			'classtypes'
			);
		if ($drop_test_tables) {
			foreach ($test_tables as $table) {
				$db->exec("DROP TABLE $table");
			}
		}

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
		return $db;
	}

	static function skip() {
		try {
			$db = PDOTest::factory();
		} catch (PDOException $e) {
			die("skip " . $e->getMessage());
		}
	}

	static function test_factory($file) {
		$data = file_get_contents($file);
		$data = preg_replace('/^.*--REDIRECTTEST--/s', '', $data);
		$config = eval($data);
		foreach ($config['ENV'] as $k => $v) {
			putenv("$k=$v");
		}
		return self::factory();
	}

	static function user_factory($classname = 'PDO', $drop_test_tables = true) {
		$dsn = self::dsn_user_default();
		$user = 'u1';
		$pass = '';
		$attr = NULL;
                
		if (is_string($attr) && strlen($attr)) {
			$attr = unserialize($attr);
		} else {
			$attr = null;
		}

		if ($user === false) $user = NULL;
		if ($pass === false) $pass = NULL;

		$db = new $classname($dsn, $user, $pass, $attr);

		if (!$db) {
			die("Could not create PDO object (DSN=$dsn, user=$user)\n");
		}

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
		
		return $db;
	}
}
?>
