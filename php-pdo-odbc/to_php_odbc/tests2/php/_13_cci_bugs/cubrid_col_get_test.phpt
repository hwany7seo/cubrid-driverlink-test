--TEST--
cubrid_schema CUBRID_SCH_ATTR_PRIVILEGE
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=" . $db, "", "");
odbc_exec($conn, "DROP TABLE if exists col2_get_tb");
odbc_exec($conn, "CREATE TABLE col2_get_tb(a int AUTO_INCREMENT, b set(int), c list(int,varchar(10)), d char(10)) DONT_REUSE_OID");
//odbc_exec($conn, "CREATE TABLE col2_get_tb(a int AUTO_INCREMENT, b set(int), c list(varchar(10)), d char(10))");
odbc_exec($conn, "INSERT INTO col2_get_tb(a, b, c, d) VALUES(1, {1,2,3}, {11,22,33,'varchar1','varchar2'}, 'a')");
//odbc_exec($conn, "INSERT INTO col2_get_tb(a, b, c, d) VALUES(1, {1,2,3}, {'varchar1','varchar2','varchar3'}, 'a')");
$req = odbc_exec($conn, "SELECT * FROM col2_get_tb", CUBRID_INCLUDE_OID);

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$oid = cubrid_current_oid($req);

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

$size = cubrid_col_size($conn, $oid, "c");
var_dump($size);

$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

$size = cubrid_col_size($conn, $oid, "b");
var_dump($size);

odbc_free_result($req);


odbc_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Warning: Error: CAS, -10021, Heterogeneous set is not supported in %s on line %d
bool(false)

Warning: Error: CAS, -10021, Heterogeneous set is not supported in %s on line %d
bool(false)
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
int(3)
Finished!
