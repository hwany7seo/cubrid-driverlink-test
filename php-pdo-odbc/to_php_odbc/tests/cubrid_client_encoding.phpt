--TEST--
cubrid_client_encoding
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

$charset = cubrid_client_encoding($conn);
printf("CUBRID current charset: %s\n", $charset);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
CUBRID current charset: %s
done!
