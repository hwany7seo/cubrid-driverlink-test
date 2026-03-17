<?php
require_once('connect.inc');
if ($skip_on_connect_failure) {
	
    if (!$conn = @odbc_connect("Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;database=demodb", "", "")) {
        die(sprintf("skip Can't connect to CUBRID DB Server - [%d] %s", cubrid_errno(), cubrid_error()));
    }

    cubrid_disconnect($conn);
}
?>
