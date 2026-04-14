--TEST--
cubrid_is_instance
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

if (!($req = odbc_exec($conn, 'SELECT * FROM code'))) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$res = cubrid_is_instance($conn, $oid);
if ($res == 1) {
    printf("Intance pointed by %s exists.\n", $oid);
} else {
    printf ("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Intance pointed by %s exists.
done!
