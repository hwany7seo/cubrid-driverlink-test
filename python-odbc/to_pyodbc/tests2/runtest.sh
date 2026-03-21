#!/bin/bash
SCRIPT_DIR=$(dirname $(readlink -f "$0"))
cd "$SCRIPT_DIR" || exit 1
# init.sh must be sourced from this directory (not the caller's cwd)
. "$SCRIPT_DIR/init.sh"

TEST_LOB_DIR=$SCRIPT_DIR/lob

db=pydb
CUBRID_LANG=ko_KR.utf8
PYTHON_CON=configuration/python_config.xml
TC_DIR_PERFORMANCE="performance"
TC_DIR="python"
testcases=python_testcase_list
MEM_LOG=memoryLeaklog
FUNC_LOG=function_result
VALGRIND="valgrind --leak-check=full"
python="python3.12"
test_mode="normal"
test_case="functional_only"
all_test_result_file=tests2.result

rm -rf memoryLeaklog
mkdir  memoryLeaklog
rm -rf function_result
mkdir  function_result
rm -f "$all_test_result_file"

get_options "$@"

python_version_major=$(python_version_check $python)
echo "python_version_major: $python_version_major"

if [ x"$python_version_major" != "x3" ];then
    echo -n "We do not support this version: "
    $python --version
    exit
fi

echo "Python DBI Test Begin... ($python), test_mode = $test_mode, test_case = $test_case"

if [ "$test_case" != "functional_only" ];then
    cubrid server stop $db
    cubrid createdb $db $CUBRID_LANG
    cubrid server restart $db
fi
cubrid server restart demodb
cubrid broker restart
brokerPort=`cubrid broker status -b|grep broker1|awk '{print $4}'`
ipaddress=$(hostname -i)


# Generate test cases from directories
rm -f $testcases
for dir in $TC_DIR
do
    find $dir -name '*.py' -print >> $testcases
done

for tc in $(cat $testcases)
do
    tcb=$(basename $tc)
    echo -n "Running TestCase ($tcb) $tc " | tee -a $all_test_result_file
    if [ $test_mode = "normal" ];then
        $python $tc >> $FUNC_LOG/$tcb.result 2>&1
    else
        $VALGRIND --log-file=$MEM_LOG/$tcb.memLeak $python $tc >> $FUNC_LOG/$tcb.result 2>&1
    fi
    verdict=$(check_verdict $FUNC_LOG/$tcb.result)
    echo $verdict | tee -a $all_test_result_file
done

if [ "$test_case" != "functional_only" ];then
    echo "Run Performance Test"
    for tc in "$TC_DIR_PERFORMANCE"/*.py
    do
        [ -f "$tc" ] || continue
        $python "$tc"
    done
    cubrid server stop $db
    cubrid deletedb $db
fi

rm -f $testcases
rm -rf $TEST_LOB_DIR

echo "Python DBI Test End"
