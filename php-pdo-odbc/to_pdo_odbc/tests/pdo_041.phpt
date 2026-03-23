--TEST--
PDO::lastInsertId
--SKIPIF--
<?php
if (!extension_loaded("pdo")) die("skip");
require_once 'pdo_test.inc';
try {
	$db = PDOTest::factory();
} catch (PDOException $e) {
	die('skip ' . $e->getMessage());
}
if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'odbc') {
	die('skip not supported: PDO::lastInsertId() — CUBRID ODBC does not support this API (SQLGetStmtAttr/SQL_LAST_INSERT_ID / IM001)');
}
unset($db);
?>
--FILE--
<?php

require_once 'pdo_test.inc';
$db = PDOTest::factory();

$db->exec("CREATE TABLE cubrid_test (id INT AUTO_INCREMENT, name varchar(20))");
$db->exec("INSERT INTO cubrid_test VALUES (1, 'A')");
$db->exec("INSERT INTO cubrid_test(name) VALUES ('B')");


$id= $db->lastInsertId('cubrid_test');

var_dump($id);

?>
--EXPECTF--
string(1) "1"
