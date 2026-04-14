--TEST--
cubrid_query_timeout
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
require_once('until.php');
?>
--FILE--
<?php

include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

$req = odbc_prepare($conn, "SELECT * FROM code");

$timeout = cubrid_get_query_timeout($req);
var_dump($timeout);

cubrid_set_query_timeout($req, 1000);
$timeout = cubrid_get_query_timeout($req);
var_dump($timeout);

odbc_close($conn);

print "done!";
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
int(5000)
int(1000)
done!
