--TEST--
odbc_next_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = odbc_connect($cubrid_odbc_dsn, "", "");

odbc_exec($conn, "drop table if exists char_enum");
odbc_exec($conn, "create table char_enum(a enum('value_a', 'value_b', 'value_c'))");

$res = odbc_prepare($conn, "insert into char_enum(a) values(?)");
odbc_execute($res, array("value_a"));

$res = odbc_prepare($conn, "insert into char_enum(a) values(?)");
odbc_execute($res, array('value_c'));

$res = odbc_prepare($conn, "insert into char_enum(a) values(?)");
$ret = odbc_execute($res, array('value_b'));
if (!$ret) {
	print "cubrid execute failed.\n";
}

$sql_stmt = "select a from char_enum where a=?";
$res = odbc_prepare($conn, $sql_stmt);
odbc_execute($res, array("value_a"));

$ret = get_result_info($res);

odbc_free_result($res);
odbc_close($conn);

print "done!";

function print_field_info($req_handle, $offset = 0)
{
	printf("\n------------ print_field_info --------------------\n");

	true;

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

	$col_num = odbc_num_fields($req_handle);
	if ($col_num < 0) {
		return false;
	}

	$field_num = odbc_num_fields($req_handle);
	assert($field_num == $col_num);

	$column_name_list = cubrid_column_names($req_handle);
	if (!$column_name_list) {
		return false;
	}

	$column_type_list = cubrid_column_types($req_handle);
	if (!$column_type_list) {
		return false;
	}

	$size = count($column_name_list);
	$column_lens = [];
	for ($i = 0; $i < $size; $i++) {
		$column_lens[] = odbc_field_len($req_handle, $i + 1);
	}

	$row_num = odbc_num_rows($req_handle);
	if ($row_num < 0) {
		$n = 0;
		while (odbc_fetch_row($req_handle)) {
			$n++;
		}
		$row_num = $n;
	}

	printf("%-30s %d\n", "Row count:", $row_num);
	printf("%-30s %d\n", "Column count:", $col_num);
	printf("\n");

	printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Len");
	printf("------------------------------------------------------------------------------\n");
	for ($i = 0; $i < $size; $i++) {
		printf("%-30s %-30s %-15s\n", $column_name_list[$i], $column_type_list[$i], $column_lens[$i]);
	}
	printf("\n\n");

	return true;
}
?>

--CLEAN--
--EXPECTF--
------------ get_result_info --------------------
Row count:                     1
Column count:                  1

Column Names                   Column Types                   Column Len     
------------------------------------------------------------------------------
a                              ENUM                           0              


done!