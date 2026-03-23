--TEST--
cubrid_next_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "");

odbc_exec($conn, "drop table if exists char_enum");
odbc_exec($conn, "create table char_enum(a enum('value_a', 'value_b', 'value_c'))");

$res = odbc_prepare($conn, "insert into char_enum(a) values(?)");
odbc_execute($res, 1, "value_a");
odbc_exec($res);

$ret = odbc_execute($res, 1, 'value_c', "ENUM");
odbc_exec($res);

# Here, failed!
$ret = odbc_execute($res, 1, 2, "ENUM");
$ret = odbc_exec($res);
if ($ret == false) {
    print "cubrid execute failed.\n";
}

$sql_stmt = "select a from char_enum where a=?";
$res = odbc_prepare($conn, $sql_stmt);
odbc_execute($res, 1, "value_a", "ENUM");
$req = odbc_exec($res, CUBRID_EXEC_QUERY_ALL);

$ret = get_result_info($res);

cubrid_free_result($res);
odbc_close($conn);

print "done!";

function print_field_info($req_handle, $offset = 0)
{
    printf("\n------------ print_field_info --------------------\n");

    cubrid_field_seek($req_handle, $offset);

    $field = cubrid_fetch_field($req_handle, $offset);
    if (!$field) {
	return false;
    }

    printf("%-30s %s\n", "name:", $field->name);
    printf("%-30s %s\n", "table:", $field->table);
    printf("%-30s \"%s\"\n", "default value:", $field->def);
    printf("%-30s %d\n", "max length:", $field->max_length);
    printf("%-30s %d\n", "not null:", $field->not_null);
    printf("%-30s %d\n", "primary key:", $field->primary_key);
    printf("%-30s %d\n", "unique key:", $field->unique_key);
    printf("%-30s %d\n", "multiple key:", $field->multiple_key);
    printf("%-30s %d\n", "numeric:", $field->numeric);
    printf("%-30s %d\n", "blob:", $field->blob);

    return true;
}

function get_result_info($req_handle)
{
    printf("\n------------ get_result_info --------------------\n");

    $row_num = odbc_num_rows($req_handle);
    if ($row_num < 0) {
	return false;
    }

    $col_num = cubrid_num_cols($req_handle);
    if ($col_num < 0) {
	return false;
    }

    $field_num = cubrid_num_fields($req_handle);
    if ($col_num < 0) {
	return false;
    }
    assert($field_num == $col_num);

    $column_name_list = cubrid_column_names($req_handle);
    if (!$column_name_list) {
	return false;
    }

    $column_type_list = cubrid_column_types($req_handle);
    if (!$column_type_list) {
	return false;
    }

    printf("%-30s %d\n", "Row count:", $row_num);
    printf("%-30s %d\n", "Column count:", $col_num);
    printf("\n");

    printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Len");
    printf("------------------------------------------------------------------------------\n");
    $size = count($column_name_list);
    for($i = 0; $i < $size; $i++) {
	$column_len = cubrid_field_len($req_handle, $i);
	printf("%-30s %-30s %-15s\n", $column_name_list[$i], $column_type_list[$i], $column_len); 
    }
    printf("\n\n");

    return true;
}
?>

--CLEAN--
--EXPECTF--
Warning: Error: DBMS, -181, %s
cubrid execute failed.

------------ get_result_info --------------------
Row count:                     1
Column count:                  1

Column Names                   Column Types                   Column Len     
------------------------------------------------------------------------------
a                              enum                           0              


done!
