--TEST--
Bug #47769 (Strange extends PDO)
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded("pdo_sqlite")) die("skip: PDO_SQLite not available");
?>
<?php
if (!extension_loaded("pdo_sqlite"))
	die("skip: PDO_SQLite not available");
?>
--FILE--
<?php

class test extends PDO
{
	protected function isProtected() {
		echo "this is a protected method.\n";
	}
	private function isPrivate() {
		echo "this is a private method.\n";
	}
    
    public function quote(string $str, int $paramtype = PDO::PARAM_STR): string|false {
    	$this->isProtected();
    	$this->isPrivate();
    	print $str ."\n";
    	return parent::quote($str, $paramtype);
	}
}

$test = new test('sqlite::memory:');
$test->quote('foo');
$test->isProtected();

?>
--EXPECTF--
this is a protected method.
this is a private method.
foo

Fatal error: Uncaught Error: Call to protected method test::isProtected() from global scope in %s
Stack trace:
#0 {main}
  thrown in %s on line %d
