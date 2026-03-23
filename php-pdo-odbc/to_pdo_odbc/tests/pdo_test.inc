<?php
# PDO test framework utilities

class PDOTest {
	// create an instance of the PDO driver, based on
	// the current environment
	//
	// PDO_ODBC_TEST_DSN: full DSN override (e.g. same as isql: odbc.ini DSN name).
	//   export PDO_ODBC_TEST_DSN='odbc:DSN=CUBRID_ODBC_Unicode'
	// PDO_ODBC_DRIVER: driver name inside Driver={...} (default: CUBRID_ODBC_Unicode)
	static function dsn_default() {
		$override = getenv('PDO_ODBC_TEST_DSN');
		if ($override !== false && $override !== '') {
			return $override;
		}
		$drv = getenv('PDO_ODBC_DRIVER');
		if ($drv === false || $drv === '') {
			$drv = 'CUBRID_ODBC_Unicode';
		}
		return "odbc:Driver={{$drv}};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb";
	}

	// user_factory DSN (PDO_ODBC_TEST_USER_DSN, else same rules as dsn_default)
	static function dsn_user_default() {
		$override = getenv('PDO_ODBC_TEST_USER_DSN');
		if ($override !== false && $override !== '') {
			return $override;
		}
		$override = getenv('PDO_ODBC_TEST_DSN');
		if ($override !== false && $override !== '') {
			return $override;
		}
		$drv = getenv('PDO_ODBC_DRIVER');
		if ($drv === false || $drv === '') {
			$drv = 'CUBRID_ODBC_Unicode';
		}
		return "odbc:Driver={{$drv}};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb";
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
