--TEST--
column
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");
//Data type is numeric
$delete_result=odbc_exec($conn, "drop class if exists numeric_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM numeric_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is numeric#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i)); 
}
printf("\n\n");

//Data type is character strings
$delete_result=odbc_exec($conn, "drop class if exists character_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM character_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is character strings#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");

//Data type is BLOB/CLOB
$delete_result=odbc_exec($conn, "drop class if exists clob_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class clob_tb(id_t varchar(64) primary key, content CLOB, image BLOB)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM clob_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is BLOB/CLOB#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");

//Data type is collection
$delete_result=odbc_exec($conn, "drop class if exists collection_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class collection_tb(sChar set(char(10)),
	sVarchar set(varchar(10)),
	sNchar set(nchar(10)),
	sNvchar set(nchar VARYING(10)),
	sBit set(bit(10)),
	sBvit set(bit VARYING(10)),
	sNumeric set(numeric),
	sInteger set(integer),
	sSmallint set(smallint),
	sMonetary set(monetary),
	sFloat set(float),
	sReal set(real),
	sDouble set(double),
	sDate set(date),
	sTime set(time),
	sTimestamp set(timestamp),
	sSet set(set),
	sMultiSet set(multiset),
	sList set(list),
	sSequence set(sequence),
        multiset_t multiset(int, CHAR(1)),
        list_t list(float, VARCHAR(1))
)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM collection_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is collection#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");

//Data type is Date/Time
$delete_result=odbc_exec($conn, "drop class if exists date_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class date_tb(date_t date, time_t time, timestamp_t timestamp, datetime_t datetime)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM date_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is Date/Time#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");

//Data type is bit strings
$delete_result=odbc_exec($conn, "drop class if exists bit_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
$create_result=odbc_exec($conn, "create class bit_tb(bit_t bit, bit2_t bit(8), bitvarying_t bit varying, bitvarying2_t bit varying(10))");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}

$result = odbc_exec($conn, "SELECT * FROM bit_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is bit strings#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");


odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####Data type is numeric#####
Field Table     Field Name      Field Type
                               smallint_t                     SMALLINT       
                               short_t                        SMALLINT       
                               int_t                          INTEGER        
                               bigint_t                       BIGINT         
                               decimal_t                      NUMERIC        
                               numeric_t                      NUMERIC        
                               float_t                        FLOAT          
                               real_t                         FLOAT          
                               monetary_t                     DOUBLE         
                               double_t                       DOUBLE         


#####Data type is character strings#####
Field Table     Field Name      Field Type
                               char_t                         CHAR           
                               varchar_t                      VARCHAR        
                               nchar_t                        CHAR           
                               ncharvarying_t                 VARCHAR        


#####Data type is BLOB/CLOB#####
Field Table     Field Name      Field Type
                               id_t                           VARCHAR        
                               content                        CLOB           
                               image                          BLOB           


#####Data type is collection#####
Field Table     Field Name      Field Type
                               schar                          VARCHAR        
                               svarchar                       VARCHAR        
                               snchar                         VARCHAR        
                               snvchar                        VARCHAR        
                               sbit                           VARCHAR        
                               sbvit                          VARCHAR        
                               snumeric                       VARCHAR        
                               sinteger                       VARCHAR        
                               ssmallint                      VARCHAR        
                               smonetary                      VARCHAR        
                               sfloat                         VARCHAR        
                               sreal                          VARCHAR        
                               sdouble                        VARCHAR        
                               sdate                          VARCHAR        
                               stime                          VARCHAR        
                               stimestamp                     VARCHAR        
                               sset                           VARCHAR        
                               smultiset                      VARCHAR        
                               slist                          VARCHAR        
                               ssequence                      VARCHAR        
                               multiset_t                     VARCHAR        
                               list_t                         VARCHAR        


#####Data type is Date/Time#####
Field Table     Field Name      Field Type
                               date_t                         DATE           
                               time_t                         TIME           
                               timestamp_t                    TIMESTAMP      
                               datetime_t                     TIMESTAMP      


#####Data type is bit strings#####
Field Table     Field Name      Field Type
                               bit_t                          BIT            
                               bit2_t                         BIT            
                               bitvarying_t                   BIT VARYING    
                               bitvarying2_t                  BIT VARYING    


Finished!