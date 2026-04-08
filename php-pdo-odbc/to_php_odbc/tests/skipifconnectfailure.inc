<?php
require_once('connect.inc');
if ($skip_on_connect_failure) {

    if (!$conn = @odbc_connect($cubrid_odbc_dsn, "", "")) {
        die(sprintf("skip Can't connect to CUBRID DB Server - [%s] %s", odbc_error(), odbc_errormsg()));
    }

    odbc_close($conn);
}
?>
