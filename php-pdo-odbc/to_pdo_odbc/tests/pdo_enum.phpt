--TEST--
PDO Common: enum type
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded('pdo')) die('skip');
require_once 'pdo_test.inc';
PDOTest::skip();
?>
--FILE--
<?php
require_once 'pdo_test.inc';
$db = PDOTest::factory();

$db->exec("drop table if exists cubrid_test");
$db->exec("create table cubrid_test (a enum('enum_a', 'enum_b', 'enum_c'))");

# Insert (PDO_ODBC: use ENUM member strings — native PDO may map PARAM_INT ordinal; ODBC does not.)
$stmt = $db->prepare('insert into cubrid_test values(:a)');
$stmt->bindParam(':a', $name, PDO::PARAM_STR);
$name = 'enum_a';
$stmt->execute();

$stmt->bindValue(':a', 'enum_b', PDO::PARAM_STR);
$stmt->execute();

$stmt->bindValue(':a', 'enum_c', PDO::PARAM_STR);
$stmt->execute();


# Query
$stmt = $db->prepare('select a FROM cubrid_test');
var_dump($stmt->execute());
var_dump($stmt->fetchAll());

$stmt = $db->prepare('select a FROM cubrid_test where a=:val');
$stmt->bindValue(':val', 'enum_b', PDO::PARAM_STR);
var_dump($stmt->execute());
var_dump($stmt->fetchAll());

$stmt = $db->prepare('select a FROM cubrid_test where a=:val');
$val = '';
$stmt->bindParam(':val', $val, PDO::PARAM_STR);
$val = 'enum_c';
var_dump($stmt->execute());
var_dump($stmt->fetchAll());

# Query column meta
$select = $db->query('SELECT * FROM cubrid_test');
$meta = $select->getColumnMeta(0);
var_dump($meta);

?>
--EXPECT--
bool(true)
array(3) {
  [0]=>
  array(2) {
    ["a"]=>
    string(6) "enum_a"
    [0]=>
    string(6) "enum_a"
  }
  [1]=>
  array(2) {
    ["a"]=>
    string(6) "enum_b"
    [0]=>
    string(6) "enum_b"
  }
  [2]=>
  array(2) {
    ["a"]=>
    string(6) "enum_c"
    [0]=>
    string(6) "enum_c"
  }
}
bool(true)
array(1) {
  [0]=>
  array(2) {
    ["a"]=>
    string(6) "enum_b"
    [0]=>
    string(6) "enum_b"
  }
}
bool(true)
array(1) {
  [0]=>
  array(2) {
    ["a"]=>
    string(6) "enum_c"
    [0]=>
    string(6) "enum_c"
  }
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(1) "a"
  ["len"]=>
  int(1073741823)
  ["precision"]=>
  int(0)
}

