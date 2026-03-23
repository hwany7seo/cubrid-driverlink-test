--TEST--
PDO CUBRID: PDOStatement::getColumnMeta
--SKIPIF--
<?php # vim:ft=php
if (!extension_loaded('pdo')) die('skip');
require_once 'pdo_test.inc';
PDOTest::skip();

/*
 * PDO_ODBC: getColumnMeta() returns pdo_type, name, len, precision only (see EXPECT).
 * Native cubrid-pdo fills 16 keys; upstream differs.
 */
?>
--FILE--
<?php
require_once 'pdo_test.inc';
$db = PDOTest::factory();

$result = $db->query('SELECT 1 FROM db_root');

var_dump($result->getColumnMeta(0));

$result = $db->query('SELECT * FROM public.game limit 1');

var_dump($result->getColumnMeta(0));
var_dump($result->getColumnMeta(1));
var_dump($result->getColumnMeta(4));
var_dump($result->getColumnMeta(6));
?>
--EXPECT--
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(1) "1"
  ["len"]=>
  int(11)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(9) "host_year"
  ["len"]=>
  int(11)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(10) "event_code"
  ["len"]=>
  int(11)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(11) "nation_code"
  ["len"]=>
  int(3)
  ["precision"]=>
  int(0)
}
array(4) {
  ["pdo_type"]=>
  int(2)
  ["name"]=>
  string(9) "game_date"
  ["len"]=>
  int(10)
  ["precision"]=>
  int(0)
}
