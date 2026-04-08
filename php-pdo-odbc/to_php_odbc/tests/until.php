<?php

function check_table_existence($conn_handle, $table_name)
{
	$sql_stmt = "SELECT class_name FROM db_class WHERE class_name = ?";
	$stmt = odbc_prepare($conn_handle, $sql_stmt);
	if (!$stmt) {
		return -1;
	}

	if (!odbc_execute($stmt, array($table_name))) {
		odbc_free_result($stmt);
		return -1;
	}

	$found = false;
	if (odbc_fetch_row($stmt)) {
		$found = true;
	}

	odbc_free_result($stmt);

	return $found ? 1 : 0;
}
