--TEST--
cubrid_pconnect
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once("connect.inc");

$tmp = NULL;
$pconn = NULL;

if (!is_null($tmp = @cubrid_pconnect())) {
    printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!is_null($tmp = @cubrid_pconnect_with_url())) {
    printf("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!($pconn = cubrid_pconnect($host, $port, $db, $user, $passwd))) {
    printf("[003] Can not connect to the server using host=%s, port=%s, user=%s, passwd=***\n", $host, $port, $user);
}

$req = odbc_exec($pconn, "SELECT * FROM code");
$row = odbc_fetch_array($req);
var_dump($row);

odbc_close($pconn);

if (!($pconn = cubrid_pconnect($host, $port, $db, $user, $passwd))) {
    printf("[004] Cannot connect, [%d] %s\n", cubrid_errno(), cubrid_error());
}

odbc_close($pconn);

if (!($pconn = cubrid_pconnect_with_url($connect_url, $user, $passwd))) {
    printf("[003] Can not connect to the server using url=%s, user=%s, passwd=***\n", $host, $port, $user);
}

$req = odbc_exec($pconn, "SELECT * FROM code");
$row = odbc_fetch_array($req);
var_dump($row);

odbc_close($pconn);

if (!($pconn = cubrid_pconnect_with_url($connect_url, $user, $passwd))) {
    printf("[004] Cannot connect, [%d] %s\n", cubrid_errno(), cubrid_error());
}

odbc_close($pconn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
array(2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
done!
