--TEST--
cubrid_lob2_new
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');

$tmp = NULL;
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

if (false !== ($tmp = @cubrid_lob2_new($conn, 'NULL'))) {
    printf('[001] Expecting boolean/false, got %s/%s\n', gettype($tmp), $tmp);
}

@odbc_exec($conn, 'DROP TABLE IF EXISTS test_lob2');

odbc_exec($conn, 'CREATE TABLE test_lob2 (id INT, images BLOB, contents CLOB)');

$req = odbc_prepare($conn, 'INSERT INTO test_lob2 VALUES (?, ?, ?)');

odbc_execute($req, 1, 1);

// The default type that cubrid_lob2_new will create is BLOB.
$lob_blob = cubrid_lob2_new();
cubrid_lob2_bind($req, 2, $lob_blob);

// If you want to create a CLOB data, you must give 'clob' to the type parameter.
$lob_clob = cubrid_lob2_new($conn, 'clob');
cubrid_lob2_bind($req, 3, $lob_clob);

odbc_exec($req);

$req = odbc_prepare($conn, 'INSERT INTO test_lob2 (images) VALUES (?)');

$lob_blob_2 = cubrid_lob2_new($conn);
cubrid_lob2_bind($req, 1, $lob_blob_2);

odbc_exec($req);

$lob_blob_3 = cubrid_lob2_new($conn, 'BLOB');
cubrid_lob2_bind($req, 1, $lob_blob_3);

odbc_exec($req);

cubrid_disconnect($conn);

print 'done!';
?>
--CLEAN--
--EXPECTF--
done!
