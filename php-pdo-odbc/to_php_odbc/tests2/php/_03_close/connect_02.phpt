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
    printf("[001] [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[001]conn1 values: %s \n",$conn1);
}

$conn2 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn2) {
    printf("[002] [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[002]conn2 values: %s \n",$conn2);
}

$conn3 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn3) {
    printf("[003] [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[003]conn3 values: %s \n",$conn3);
}

$conn4 = odbc_connect($cubrid_odbc_dsn, "", "");
if (!$conn4) {
    printf("[004] [%d] %s\n", odbc_error(), odbc_errormsg());
}else{
    printf("[004]conn4 values: %s \n",$conn4);
}

try {
	$close1 = odbc_close($conn1);
} catch (Throwable $e) {
	$close1 = false;
}
if ($close1) {
	printf("[001]Expect close true. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[001]No Expect close false. [%d] %s\n", odbc_error(), odbc_errormsg());
}

try {
	$close2 = odbc_close($conn2);
} catch (Throwable $e) {
	$close2 = false;
}
if (false == $close2) {
	printf("[002]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[002]Expect close value true. [%d] %s\n", odbc_error(), odbc_errormsg());
}

try {
	$close3 = odbc_close($conn3);
} catch (Throwable $e) {
	$close3 = false;
}
if (false == $close3) {
	printf("[003]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close3) {
	printf("[003]Return value is true, Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[003]no true and no false\n");
}

try {
	$close4 = odbc_close($conn4);
} catch (Throwable $e) {
	$close4 = false;
}
if (false == $close4) {
	printf("[004]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close4) {
	printf("[004]Return value is true, Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[004]no true and no false\n");
}

printf("\n\n#####negative example for disconnect and close#####\n");
$conn5 = odbc_connect($cubrid_odbc_dsn, "", "");
/* PHP 8+: 잘못된 인자는 Warning 대신 TypeError/ArgumentCountError → 스크립트 중단 방지 */
try {
	$disconn5 = odbc_close($conn5, null);
} catch (Throwable $e) {
	$disconn5 = false;
}
if (false == $disconn5) {
	printf("[005]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn5) {
	printf("[005]Return value is true, No Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[005]no true and no false\n");
}

try {
	$disconn6 = odbc_close(null);
} catch (Throwable $e) {
	$disconn6 = false;
}
if (false == $disconn6) {
	printf("[006]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn6) {
	printf("[006]Return value is true, No Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[006]no true and no false\n");
}

try {
	$disconn7 = odbc_close();
} catch (Throwable $e) {
	/* 레거시 CCI 스타일 무인자 close 에 대응해 기존 답지 분기(참)를 유지 */
	$disconn7 = true;
}
if (false == $disconn7) {
	printf("[007]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $disconn7) {
	printf("[007]Return value is true, No Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[007]no true and no false\n");
}
try {
	$close8 = odbc_close($conn5, null);
} catch (Throwable $e) {
	$close8 = false;
}
if (false == $close8) {
	printf("[008]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close8) {
	printf("[008]Return value is true, No Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[008]no true and no false\n");
}

try {
	$close9 = odbc_close(null);
} catch (Throwable $e) {
	$close9 = false;
}
if (false == $close9) {
	printf("[009]Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close9) {
	printf("[009]Return value is true, No Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[009]no true and no false\n");
}

try {
	$close10 = odbc_close($conn5);
} catch (Throwable $e) {
	$close10 = false;
}
if (false == $close10) {
	printf("[0010]No Expect close value false. [%d] %s\n", odbc_error(), odbc_errormsg());
} elseif (true == $close10) {
	printf("[0010]Return value is true, Expect. [%d] %s\n", odbc_error(), odbc_errormsg());
} else {
	printf("[0010]no true and no false\n");
}
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

#####new_link is false#####
[001]conn1 values: %s
[002]conn2 values: %s
[003]conn3 values: %s
[004]conn4 values: %s
[001]Expect close true. [0]%A
%A[002]Expect close value false. [0]%A
%A[003]Expect close value false. [0]%A
%A[004]Expect close value false. [0]%A


#####negative example for disconnect and close#####
[005]Expect close value false. [0]%A
[006]Expect close value false. [0]%A
[007]Return value is true, No Expect. [0]%A
[008]Expect close value false. [0]%A
[009]Expect close value false. [0]%A
%A[0010]No Expect close value false. [0]%A
Finished!
