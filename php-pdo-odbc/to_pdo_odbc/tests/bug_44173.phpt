--TEST--
PDO Common: Bug #44173 (PDO->query() parameter parsing/checking needs an update)
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded('pdo')) die('skip');
require_once 'pdo_test.inc';
PDOTest::skip();
?>
--FILE--
<?php
require getenv('REDIR_TEST_DIR') . 'pdo_test.inc';
$db = PDOTest::factory();

$db->exec("CREATE TABLE cubrid_test (x int)");
$db->exec("INSERT INTO cubrid_test VALUES (1)");


// Bug entry [1] omitted: PHP 8+ throws ArgumentCountError for query() with 0 args (not a bool(false) warning).

$try_query = static function (PDO $db, callable $call): void {
	try {
		$stmt = $call();
		var_dump($stmt);
	} catch (Throwable $e) {
		echo 'Warning: PDO::query(): ', $e->getMessage(), "\n";
		var_dump(false);
	}
};

// Bug entry [2] -- 1 is PDO::FETCH_LAZY
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", PDO::FETCH_LAZY, 0, 0));

// Bug entry [3]
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", 'abc'));

// Bug entry [4]
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", PDO::FETCH_CLASS, 0, 0, 0));

// Bug entry [5]
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", PDO::FETCH_INTO));

// Bug entry [6]
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", PDO::FETCH_COLUMN));

// Bug entry [7]
$try_query($db, fn () => $db->query("SELECT * FROM cubrid_test", PDO::FETCH_CLASS));


?>
--EXPECTF--
Warning: PDO::query(): %s
bool(false)
Warning: PDO::query(): %s
bool(false)
Warning: PDO::query(): %s
bool(false)
Warning: PDO::query(): %s
bool(false)
Warning: PDO::query(): %s
bool(false)
Warning: PDO::query(): %s
bool(false)

