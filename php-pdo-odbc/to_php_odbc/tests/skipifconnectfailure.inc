<?php
require_once('connect.inc');
if ($skip_on_connect_failure) {

    if (!$conn = @odbc_connect("Driver={CUBRID Driver};server=test-db-server;port=33000;uid=dba;pwd=;database=demodb", "", "")) {
        die(sprintf("skip Can't connect to CUBRID DB Server - [%s] %s", odbc_error(), odbc_errormsg()));
    }

    odbc_close($conn);
}
?>
