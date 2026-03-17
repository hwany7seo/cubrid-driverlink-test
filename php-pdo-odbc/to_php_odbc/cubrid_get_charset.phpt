--TEST--
cubrid_get_charset
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

$charset = cubrid_get_charset($conn);
var_dump($charset);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
string(5) "utf-8"
done!
