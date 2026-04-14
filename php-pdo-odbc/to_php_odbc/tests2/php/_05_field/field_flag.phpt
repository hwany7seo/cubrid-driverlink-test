--TEST--
cubrid_column_names/types/len via ODBC (field_flag suite)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = odbc_connect($cubrid_odbc_dsn, "", "");

/**
 * Get column size map for a table via odbc_columns() (SQLColumns catalog function).
 * Returns: [ lowercase_column_name => COLUMN_SIZE, ... ]
 *
 * NOTE: odbc_field_len() / cubrid_field_len() use SQL_COLUMN_PRECISION (deprecated ODBC 2.x
 * attribute). For string/binary/date types this either wraps in a 16-bit short causing
 * overflow, or returns semantically wrong values (e.g. precision=0 for DATE).
 * odbc_columns() exposes COLUMN_SIZE from SQLColumns(), which is ODBC 3.x-compliant and
 * maps to SQL_DESC_LENGTH without the short overflow issue.
 */
function get_column_size_map($conn, $table_name) {
    $size_map = [];
    $col_meta = odbc_columns($conn, null, null, $table_name, '%');
    if ($col_meta) {
        while (odbc_fetch_row($col_meta)) {
            $col_name = strtolower(odbc_result($col_meta, 'COLUMN_NAME'));
            $size_map[$col_name] = odbc_result($col_meta, 'COLUMN_SIZE');
        }
        odbc_free_result($col_meta);
    }
    return $size_map;
}

//Data type is numeric
$delete_result1=odbc_exec($conn, "drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result1);
$create_result1=odbc_exec($conn, "create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_free_result($create_result1);

$result1 = odbc_exec($conn, "SELECT * FROM numeric_tb;");
$column_names1 = cubrid_column_names($result1);
$column_types1 = cubrid_column_types($result1);
$size_map1 = get_column_size_map($conn, 'numeric_tb');

printf("#####Data type is numeric#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
    $colkey = strtolower($column_names1[$i]);
    $column_len1 = $size_map1[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names1[$i], $column_types1[$i], $column_len1);
}
printf("\n\n");

//Data type is character strings
$delete_result2=odbc_exec($conn, "drop class if exists character_tb");
if (!$delete_result2) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result2);
$create_result2=odbc_exec($conn, "create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result2) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_free_result($create_result2);

$result2 = odbc_exec($conn, "SELECT * FROM character_tb;");
$column_names2 = cubrid_column_names($result2);
$column_types2 = cubrid_column_types($result2);
$size_map2 = get_column_size_map($conn, 'character_tb');

printf("#####Data type is character strings#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names2); $i < $size; $i++) {
    $colkey = strtolower($column_names2[$i]);
    $column_len2 = $size_map2[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names2[$i], $column_types2[$i], $column_len2);
}
printf("\n\n");

//Data type is BLOB/CLOB
$delete_result=odbc_exec($conn, "drop class if exists clob_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result);
$create_result=odbc_exec($conn, "create class clob_tb(id_t varchar(64) primary key, content CLOB, image BLOB)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_free_result($create_result);

$result = odbc_exec($conn, "SELECT * FROM clob_tb;");
$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);
$size_map = get_column_size_map($conn, 'clob_tb');

printf("#####Data type is BLOB/CLOB#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $colkey = strtolower($column_names[$i]);
    $column_len = $size_map[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is collection
$delete_result=odbc_exec($conn, "drop class if exists collection_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result);
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
odbc_free_result($create_result);

$result = odbc_exec($conn, "SELECT * FROM collection_tb;");
$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);
$size_map = get_column_size_map($conn, 'collection_tb');

printf("#####Data type is collection#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $colkey = strtolower($column_names[$i]);
    $column_len = $size_map[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is Date/Time
$delete_result=odbc_exec($conn, "drop class if exists date_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result);
$create_result=odbc_exec($conn, "create class date_tb(date_t date, time_t time, timestamp_t timestamp, datetime_t datetime)");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_free_result($create_result);

$result = odbc_exec($conn, "SELECT * FROM date_tb;");
$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);
$size_map = get_column_size_map($conn, 'date_tb');

printf("#####Data type is Date/Time#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $colkey = strtolower($column_names[$i]);
    $column_len = $size_map[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is bit strings
$delete_result=odbc_exec($conn, "drop class if exists bit_tb");
if (!$delete_result) {
    die('Delete Failed: ' . odbc_errormsg());
}
odbc_free_result($delete_result);
$create_result=odbc_exec($conn, "create class bit_tb(bit_t bit, bit2_t bit(8), bitvarying_t bit varying, bitvarying2_t bit varying(10))");
if (!$create_result) {
    die('Create Failed: ' . odbc_errormsg());
}
odbc_free_result($create_result);

$result = odbc_exec($conn, "SELECT * FROM bit_tb;");
$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);
$size_map = get_column_size_map($conn, 'bit_tb');

printf("#####Data type is bit strings#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $colkey = strtolower($column_names[$i]);
    $column_len = $size_map[$colkey] ?? 'N/A';
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");


odbc_close($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####Data type is numeric#####
Column Names                   Column Types                   Column Maxlen  
smallint_t                     SMALLINT                       5              
short_t                        SMALLINT                       5              
int_t                          INTEGER                        10             
bigint_t                       BIGINT                         19             
decimal_t                      NUMERIC                        15             
numeric_t                      NUMERIC                        38             
float_t                        FLOAT                          15             
real_t                         FLOAT                          15             
monetary_t                     DOUBLE                         22             
double_t                       DOUBLE                         22             


#####Data type is character strings#####
Column Names                   Column Types                   Column Maxlen  
char_t                         CHAR                           5              
varchar_t                      VARCHAR                        11             
nchar_t                        CHAR                           20             
ncharvarying_t                 VARCHAR                        536870911      


#####Data type is BLOB/CLOB#####
Column Names                   Column Types                   Column Maxlen  
id_t                           VARCHAR                        64             
content                        CLOB                           0              
image                          BLOB                           0              


#####Data type is collection#####
Column Names                   Column Types                   Column Maxlen  
schar                          VARCHAR                        0              
svarchar                       VARCHAR                        0              
snchar                         VARCHAR                        0              
snvchar                        VARCHAR                        0              
sbit                           VARCHAR                        0              
sbvit                          VARCHAR                        0              
snumeric                       VARCHAR                        0              
sinteger                       VARCHAR                        10             
ssmallint                      VARCHAR                        5              
smonetary                      VARCHAR                        22             
sfloat                         VARCHAR                        15             
sreal                          VARCHAR                        15             
sdouble                        VARCHAR                        22             
sdate                          VARCHAR                        10             
stime                          VARCHAR                        12             
stimestamp                     VARCHAR                        23             
sset                           VARCHAR                        1073741823     
smultiset                      VARCHAR                        0              
slist                          VARCHAR                        0              
ssequence                      VARCHAR                        0              
multiset_t                     VARCHAR                        0              
list_t                         VARCHAR                        0              


#####Data type is Date/Time#####
Column Names                   Column Types                   Column Maxlen  
date_t                         DATE                           10             
time_t                         TIME                           12             
timestamp_t                    TIMESTAMP                      23             
datetime_t                     TIMESTAMP                      23             


#####Data type is bit strings#####
Column Names                   Column Types                   Column Maxlen  
bit_t                          BIT                            1              
bit2_t                         BIT                            8              
bitvarying_t                   BIT VARYING                    1073741823     
bitvarying2_t                  BIT VARYING                    10             


Finished!