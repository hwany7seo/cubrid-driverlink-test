--TEST--
cubrid_schema CUBRID_SCH_IMPORTED_KEYS
--SKIPIF--
<?php
require_once('skipif.inc');
require_once 'skipif_cubrid_extension_only_api.inc';
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//CUBRID_SCH_IMPORTED_KEYS
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

print("#####positive example#####\n");
printf("ssss table has two foreign keys\n"); 
odbc_exec($conn,"drop table if EXISTS ssss;");
odbc_exec($conn,"drop table if EXISTS aaaa;");
odbc_exec($conn,"drop table if EXISTS album;");
odbc_exec($conn,"CREATE TABLE album(id CHAR(10) primary key,title VARCHAR(100), artist VARCHAR(100));");
odbc_exec($conn,"CREATE TABLE aaaa(aid CHAR(10), uid int primary key);");
odbc_exec($conn,"CREATE TABLE ssss(album CHAR(10),dsk INTEGER,FOREIGN KEY (album) REFERENCES album(id), FOREIGN KEY (dsk) REFERENCES aaaa(uid));");

$schema1 = cubrid_schema($conn,CUBRID_SCH_IMPORTED_KEYS,"ssss");
var_dump($schema1);


printf("\ncccc table has been referenced by two table as foreign key\n");
odbc_exec($conn,"drop table if EXISTS  dddd;");
odbc_exec($conn,"drop table if EXISTS  eeee;");
odbc_exec($conn,"drop table if EXISTS  cccc;");
odbc_exec($conn,"CREATE TABLE cccc(id CHAR(10) primary key,title VARCHAR(100), artist VARCHAR(100));");
odbc_exec($conn,"CREATE TABLE eeee(aid CHAR(10),FOREIGN KEY (aid) REFERENCES cccc(id));");
odbc_exec($conn,"CREATE TABLE dddd(album CHAR(10),dsk INTEGER,posn INTEGER, song VARCHAR(255),FOREIGN KEY (album) REFERENCES cccc(id));");


$schema2 = cubrid_schema($conn,CUBRID_SCH_IMPORTED_KEYS,"eeee");
var_dump($schema2);

print("\n\n#####negative example#####\n");
//aaaa don't contain foreign key
$schema1 = cubrid_schema($conn,CUBRID_SCH_IMPORTED_KEYS,"aaaa");
if ($schema1 == false) {
    printf("[001] Expecting false, got [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
    printf("schema value1: \n");
    var_dump($schema1);
}

$schema2 = cubrid_schema($conn,CUBRID_SCH_IMPORTED_KEYS);
if ($schema2 == false) {
    printf("[002] Expecting false, got [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
    printf("schema value2: \n");
    var_dump($schema2);
}

$schema3 = cubrid_schema($conn,CUBRID_SCH_IMPORTED_KEYS,"nothis table");
if ($schema3 == false) {
    printf("[003] Expecting false, got [%d] [%s]\n",odbc_error(),odbc_errormsg());
}else{
    printf("schema value3: \n");
    var_dump($schema3);
}


odbc_exec($conn,"drop table if EXISTS ssss;");
odbc_exec($conn,"drop table if EXISTS aaaa;");
odbc_exec($conn,"drop table if EXISTS album;");

odbc_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
ssss table has two foreign keys
array(2) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(8) "dba.aaaa"
    ["PKCOLUMN_NAME"]=>
    string(3) "uid"
    ["FKTABLE_NAME"]=>
    string(4) "ssss"
    ["FKCOLUMN_NAME"]=>
    string(3) "dsk"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(11) "fk_ssss_dsk"
    ["PK_NAME"]=>
    string(11) "pk_aaaa_uid"
  }
  [1]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(9) "dba.album"
    ["PKCOLUMN_NAME"]=>
    string(2) "id"
    ["FKTABLE_NAME"]=>
    string(4) "ssss"
    ["FKCOLUMN_NAME"]=>
    string(5) "album"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(13) "fk_ssss_album"
    ["PK_NAME"]=>
    string(11) "pk_album_id"
  }
}

cccc table has been referenced by two table as foreign key
array(1) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(8) "dba.cccc"
    ["PKCOLUMN_NAME"]=>
    string(2) "id"
    ["FKTABLE_NAME"]=>
    string(4) "eeee"
    ["FKCOLUMN_NAME"]=>
    string(3) "aid"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(11) "fk_eeee_aid"
    ["PK_NAME"]=>
    string(10) "pk_cccc_id"
  }
}


#####negative example#####
[001] Expecting false, got [0] []

Warning: Error: CAS, -10004, Invalid argument in %s on line %d
[002] Expecting false, got [-10004] [Invalid argument]
[003] Expecting false, got [0] []
Finished!
