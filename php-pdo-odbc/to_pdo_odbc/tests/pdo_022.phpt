--TEST--
PDO Common: PDOStatement::getColumnMeta
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded('pdo')) die('skip');
require_once 'pdo_test.inc';
PDOTest::skip();

/*
 * Note well: meta information is a nightmare to handle portably.
 * PDO_ODBC (unixODBC + CUBRID ODBC) exposes a small subset via getColumnMeta()
 * (pdo_type, name, len, precision). Native cubrid-pdo fills many more keys; see upstream tests_74.
 */
?>
--FILE--
<?php
require_once 'pdo_test.inc';
$db = PDOTest::factory();

$db->exec('CREATE TABLE cubrid_test(id INT NOT NULL PRIMARY KEY, val VARCHAR(10), val2 VARCHAR(16))');
//$db->exec('insert2', "INSERT INTO cubrid_test VALUES(:first, :second, :third)"); 

$data = array(
    array('10', 'Abc', 'zxy'),
    array('20', 'Def', 'wvu'),
    array('30', 'Ghi', 'tsr'),
    array('40', 'Jkl', 'qpo'),
    array('50', 'Mno', 'nml'),
    array('60', 'Pqr', 'kji'),
);


// Insert using question mark placeholders
$stmt = $db->prepare("INSERT INTO cubrid_test VALUES(?, ?, ?)");
foreach ($data as $row) {
    $stmt->execute($row);
}

// Retrieve column metadata for a result set returned by explicit SELECT
$select = $db->query('SELECT id, val, val2 FROM cubrid_test');
$meta = $select->getColumnMeta(0);
var_dump($meta);
$meta = $select->getColumnMeta(1);
var_dump($meta);
$meta = $select->getColumnMeta(2);
var_dump($meta);

// Retrieve column metadata for a result set returned by a function
$select = $db->query('SELECT COUNT(*) FROM cubrid_test');
$meta = $select->getColumnMeta(0);
var_dump($meta);

?>
--EXPECT--
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(2) "id"
  ["len"]=>
  int(11)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(3) "val"
  ["len"]=>
  int(10)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(4) "val2"
  ["len"]=>
  int(16)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(8) "count(*)"
  ["len"]=>
  int(20)
  ["precision"]=>
  int(0)
}

