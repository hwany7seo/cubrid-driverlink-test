--TEST--
cubrid_list_dbs
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

$db_list = cubrid_list_dbs($conn);
$i = 0;
$cnt = count($db_list);
while ($i < $cnt) {
    $db_name = cubrid_db_name($db_list, $i);
    if ($db_name == "demodb") {
        printf("database name: %s\n", $db_name);
    }   
    $i++;
}

odbc_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
database name: demodb
done!
