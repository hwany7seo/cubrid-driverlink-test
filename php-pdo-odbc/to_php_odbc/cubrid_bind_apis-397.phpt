--TEST--
odbc_execute
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
require_once('until.php')
?>
--FILE--
<?php
include_once('connect.inc');

$tmp = NULL;
$conn = odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "");

@odbc_exec($conn, 'DROP TABLE bind_test');
odbc_exec($conn, 'CREATE TABLE bind_test(c1 varchar(10))');

$req = odbc_prepare($conn, 'INSERT INTO bind_test(c1) VALUES(?)');

odbc_execute($req, 1, null);
odbc_exec($req);

odbc_execute($req, 1, '1234');
odbc_exec($req);

odbc_execute($req, 1, null, "null");
odbc_exec($req);

$req = odbc_exec($conn, "SELECT * FROM bind_test");
while ($row = cubrid_fetch_assoc($req)) {
    if ($row["c1"]) {
        printf("%s\n", $row["c1"]);
    } else {
        printf("NULL\n");    
    }
}

print 'done!';
?>
--CLEAN--
<?php
require_once("clean_table.inc");
?>
--EXPECTF--
NULL
1234
NULL
done!
