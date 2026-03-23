<?php

function check_table_existence($conn_handle, $table_name)
{
    $sql_stmt = "SELECT class_name FROM db_class WHERE class_name = ?";
    $cubrid_req = odbc_prepare($conn_handle, $sql_stmt);
    if (!$cubrid_req) {
	return -1;
    }

    $cubrid_retval = odbc_execute($cubrid_req, 1, $table_name);
    if (!$cubrid_retval) {
	odbc_close_request($cubrid_req);
	return -1;
    }
     
    $cubrid_retval = odbc_exec($cubrid_req);
    if (!$cubrid_retval) {
	odbc_close_request($cubrid_req);
	return -1;
    }

    $row_num = odbc_num_rows($cubrid_req);
    if ($row_num < 0) {
	odbc_close_request($cubrid_req);
	return -1;
    }
    
    odbc_close_request($cubrid_req);

    if ($row_num > 0) {
	return 1;
    } else {
	return 0;
    }
}
?>
