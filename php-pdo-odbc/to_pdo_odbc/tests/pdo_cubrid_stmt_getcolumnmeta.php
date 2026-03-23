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
