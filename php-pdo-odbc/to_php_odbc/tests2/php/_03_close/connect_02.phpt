--TEST-- 
cubrid_connect $new_link = false cubrid_disconnect cubrid_close
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
printf("#####positive example#####\n");
printf("\n#####new_link is false#####\n");
$conn1 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn1) {
    printf("[001] [%s] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[001]conn1 is active\n");
}

$conn2 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn2) {
    printf("[002] [%s] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[002]conn2 is active\n");
}

$conn3 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn3) {
    printf("[003] [%s] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[003]conn3 is active\n");
}

$conn4 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn4) {
    printf("[004] [%s] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[004]conn4 is active\n");
}

try {
	odbc_close($conn1);
	$close1 = true;
} catch (Throwable $e) {
	$close1 = false;
}
if ($close1) {
	printf("[001]Expect close true. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[001]No Expect close false. [%s] %s\n", odbc_error(), odbc_errormsg());
}

try {
	odbc_close($conn2);
	$close2 = true;
} catch (Throwable $e) {
	$close2 = false;
}
if (false == $close2) {
	printf("[002]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[002]Expect close value true. [%s] %s\n", odbc_error(), odbc_errormsg());
}

try {
	odbc_close($conn3);
	$close3 = true;
} catch (Throwable $e) {
	$close3 = false;
}
if (false == $close3) {
	printf("[003]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close3) {
	printf("[003]Return value is true, Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[003]no true and no false\n");
}

try {
	odbc_close($conn4);
	$close4 = true;
} catch (Throwable $e) {
	$close4 = false;
}
if (false == $close4) {
	printf("[004]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close4) {
	printf("[004]Return value is true, Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[004]no true and no false\n");
}

printf("\n\n#####negative example for disconnect and close#####\n");
$conn5 = odbc_connect($cubrid_odbc_dsn, "", "");
try {
	odbc_close($conn5, null);
	$disconn5 = true;
} catch (Throwable $e) {
	$disconn5 = false;
}
if (false == $disconn5) {
	printf("[005]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn5) {
	printf("[005]Return value is true, No Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[005]no true and no false\n");
}

try {
	odbc_close(null);
	$disconn6 = true;
} catch (Throwable $e) {
	$disconn6 = false;
}
if (false == $disconn6) {
	printf("[006]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn6) {
	printf("[006]Return value is true, No Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[006]no true and no false\n");
}

try {
	odbc_close();
	$disconn7 = true;
} catch (Throwable $e) {
	$disconn7 = false;
}
if (false == $disconn7) {
	printf("[007]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn7) {
	printf("[007]Return value is true, No Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[007]no true and no false\n");
}
try {
	odbc_close($conn5, null);
	$close8 = true;
} catch (Throwable $e) {
	$close8 = false;
}
if (false == $close8) {
	printf("[008]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close8) {
	printf("[008]Return value is true, No Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[008]no true and no false\n");
}

try {
	odbc_close(null);
	$close9 = true;
} catch (Throwable $e) {
	$close9 = false;
}
if (false == $close9) {
	printf("[009]Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close9) {
	printf("[009]Return value is true, No Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[009]no true and no false\n");
}

try {
	odbc_close($conn5);
	$close10 = true;
} catch (Throwable $e) {
	$close10 = false;
}
if (false == $close10) {
	printf("[0010]No Expect close value false. [%s] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close10) {
	printf("[0010]Return value is true, Expect. [%s] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[0010]no true and no false\n");
}
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

#####new_link is false#####
[001]conn1 is active
[002]conn2 is active
[003]conn3 is active
[004]conn4 is active
[001]Expect close true. [%a] %a
[002]Expect close value false. [%a] %a
[003]Expect close value false. [%a] %a
[004]Expect close value false. [%a] %a


#####negative example for disconnect and close#####
[005]Expect close value false. [%a] %a
[006]Expect close value false. [%a] %a
[007]Expect close value false. [%a] %a
[008]Expect close value false. [%a] %a
[009]Expect close value false. [%a] %a
[0010]Return value is true, Expect. [%a] %a
Finished!
