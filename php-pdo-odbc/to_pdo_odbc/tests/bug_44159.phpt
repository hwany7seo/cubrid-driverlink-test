--TEST--
Bug #44159 (Crash: $pdo->setAttribute(PDO::STATEMENT_ATTR_CLASS, NULL))
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded('pdo')) die('skip');
try {
	$pdo = new PDO("sqlite:".__DIR__."/foo.db");
} catch (Exception $e) {
	die("skip PDP_SQLITE not available");
}
?>
--FILE--
<?php
$pdo = new PDO("sqlite:".__DIR__."/foo.db");

$attrs = array(PDO::ATTR_STATEMENT_CLASS, PDO::ATTR_STRINGIFY_FETCHES, PDO::NULL_TO_STRING);

foreach ($attrs as $attr) {
	var_dump($pdo->setAttribute($attr, NULL));
	var_dump($pdo->setAttribute($attr, 1));
	var_dump($pdo->setAttribute($attr, 'nonsense'));
}

@unlink(__DIR__."/foo.db");

?>
--EXPECTF--
Fatal error: Uncaught TypeError: PDO::ATTR_STATEMENT_CLASS value must be of type array, null given in %s:%d
Stack trace:
#0 %a
#1 {main}
  thrown in %s on line %d
