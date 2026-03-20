"""
CUBRID ODBC + pyodbc — same workload as
linux/python/cubrid-python/tests2/performance/perf_4_python.py (CUBRIDdb).

Semantics: loop count, limit_num logic, print messages, 10-thread orchestration.

pyodbc / CUBRID ODBC notes:
- INSERT: default uses TIMESTAMP as SQL string literal (no ? bind). Several driver
  builds return HY000 for SYSDATETIME literals and for bound Python datetime.
- PERF_SERVER_TS=1: server expr (PERF_TIMESTAMP_EXPR, default SYSDATETIME).
- PERF_USE_PARAM_TS=1: VALUES (?, ?, ?, ?) + datetime bind (often broken).
- SELECT: results drained via fetchmany.
- test_one_thread: runs insert→select→update→delete on ONE ODBC connection (native
  uses a thread per phase; ODBC is more reliable without extra sessions here).

  PERF_LOOP_COUNT=10000 python perf_4_python.py
"""
import datetime
import os
import threading
import time
import pyodbc
from time import ctime
from xml.dom import minidom

PERF_LOOP_COUNT = int(os.environ.get("PERF_LOOP_COUNT", "10000000"))
PERF_DEBUG_SQL = os.environ.get("PERF_DEBUG_SQL", "").lower() in ("1", "true", "yes")
PERF_SERVER_TS = os.environ.get("PERF_SERVER_TS", "").lower() in ("1", "true", "yes")
PERF_USE_PARAM_TS = os.environ.get("PERF_USE_PARAM_TS", "").lower() in ("1", "true", "yes")
PERF_TIMESTAMP_EXPR = os.environ.get("PERF_TIMESTAMP_EXPR", "SYSDATETIME").strip()

# Slightly wider than native varchar(20): avoids edge truncation with some charsets.
DDL_TDB = "create table tdb(a int, b varchar(64), c timestamp, e int)"


def _config_xml_path():
    return os.path.normpath(
        os.path.join(
            os.path.dirname(os.path.abspath(__file__)),
            "..",
            "configuration",
            "python_config.xml",
        )
    )


def _xml_text(elem):
    if elem is None or elem.firstChild is None:
        return ""
    n = elem.firstChild
    if n.nodeType == n.TEXT_NODE:
        return n.data.strip()
    return (n.toxml() if hasattr(n, "toxml") else str(n)).strip()


def getConStr():
    xmlt = minidom.parse(_config_xml_path())
    root = xmlt.documentElement
    ip = _xml_text(root.getElementsByTagName("ip")[0])
    port = _xml_text(root.getElementsByTagName("port")[0])
    dbname = _xml_text(root.getElementsByTagName("dbname")[0])
    return (
        "DRIVER={CUBRID ODBC Driver};SERVER="
        + ip
        + ";PORT="
        + port
        + ";UID=dba;PWD=;DB_NAME="
        + dbname
    )


def _drain_cursor(cur):
    while True:
        rows = cur.fetchmany(10000)
        if not rows:
            break


def _connect(con_str):
    conn = pyodbc.connect(con_str, timeout=30)
    conn.autocommit = True
    return conn


def _insert_test_body(cur):
    print("start insert...")
    each_st_time = time.time()
    b_val = "systimestamp +  pefor"
    b_lit = b_val.replace("'", "''")
    if PERF_SERVER_TS:
        for n in range(PERF_LOOP_COUNT):
            sql = (
                "INSERT INTO tdb (a, b, c, e) VALUES ("
                + str(n)
                + ", '"
                + b_lit
                + "', "
                + PERF_TIMESTAMP_EXPR
                + ", "
                + str(n)
                + ")"
            )
            if PERF_DEBUG_SQL and n == 0:
                print(sql)
            cur.execute(sql)
    elif PERF_USE_PARAM_TS:
        sql_ins = "INSERT INTO tdb (a, b, c, e) VALUES (?, ?, ?, ?)"
        for n in range(PERF_LOOP_COUNT):
            if PERF_DEBUG_SQL and n == 0:
                print(sql_ins, (n, b_val, "<datetime>", n))
            cur.execute(sql_ins, (n, b_val, datetime.datetime.now(), n))
    else:
        # Default: CUBRID accepts 'YYYY-MM-DD HH:MM:SS' for TIMESTAMP column.
        for n in range(PERF_LOOP_COUNT):
            ts_lit = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            sql = (
                "INSERT INTO tdb (a, b, c, e) VALUES ("
                + str(n)
                + ", '"
                + b_lit
                + "', '"
                + ts_lit
                + "', "
                + str(n)
                + ")"
            )
            if PERF_DEBUG_SQL and n == 0:
                print(sql)
            cur.execute(sql)
    each_ed_time = time.time()
    eslap_time = each_ed_time - each_st_time
    print(
        "**The operation is insert, and the elapse time for insert:"
        + "%f" % eslap_time
        + "sec."
    )
    print()


def _delete_test_body(cur):
    print("start delete...")
    each_st_time = time.time()
    for n in range(PERF_LOOP_COUNT):
        if n == 0 or n == 1:
            limit_num = 1
        else:
            limit_num = n * 100
        sql = "delete from tdb where a<" + "%d" % limit_num
        print(sql)
        cur.execute(sql)
    each_ed_time = time.time()
    eslap_time = each_ed_time - each_st_time
    print(
        "**The operation is delete, and the elapse time for one commit:"
        + "%f" % eslap_time
        + "sec."
    )
    print()


def _update_test_body(cur):
    print("start update...")
    each_st_time = time.time()
    for n in range(PERF_LOOP_COUNT):
        sql = "update tdb set e = e + 10000000 where a=" + "%d" % n
        cur.execute(sql)
    each_ed_time = time.time()
    eslap_time = each_ed_time - each_st_time
    print(
        "**The operation is update, and the elapse time for one commit:"
        + "%f" % eslap_time
        + "sec."
    )
    print()


def _select_test_body(cur):
    print("start select...")
    each_st_time = time.time()
    for n in range(PERF_LOOP_COUNT):
        if n == 0 or n == 1:
            limit_num = 2
        else:
            limit_num = (n + 1) * 100
        sql = "select * from tdb where a <" + "%d" % limit_num
        cur.execute(sql)
        _drain_cursor(cur)
    each_ed_time = time.time()
    eslap_time = each_ed_time - each_st_time
    print(
        "**The operation is select, and the elapse for each time:"
        + "%f" % eslap_time
        + "sec."
    )
    print()


class MyThread(threading.Thread):
    def __init__(self, name=""):
        threading.Thread.__init__(self)
        self.name = name

    def run(self):
        con_str = getConStr()
        self.conn = _connect(con_str)
        self.cur = self.conn.cursor()
        start_time = time.time()
        if self.name == "insert":
            _insert_test_body(self.cur)
        elif self.name == "delete":
            _delete_test_body(self.cur)
        elif self.name == "update":
            _update_test_body(self.cur)
        elif self.name == "select":
            _select_test_body(self.cur)
        end_time = time.time()
        elapse_time = end_time - start_time
        print(
            "**The operation - "
            + self.name
            + " ,total elapse time:"
            + "%f" % elapse_time
            + " sec."
        )
        self.cur.close()
        self.conn.close()


def test_one_thread():
    con_str = getConStr()
    conn = _connect(con_str)
    cur = conn.cursor()
    cur.execute("drop table if exists tdb")
    cur.execute(DDL_TDB)

    print("starting one thread operation at:", ctime())
    for phase in ("insert", "select", "update", "delete"):
        t0 = time.time()
        if phase == "insert":
            _insert_test_body(cur)
        elif phase == "select":
            _select_test_body(cur)
        elif phase == "update":
            _update_test_body(cur)
        elif phase == "delete":
            _delete_test_body(cur)
        print(
            "**The operation - "
            + phase
            + " ,total elapse time:"
            + "%f" % (time.time() - t0)
            + " sec."
        )

    time.sleep(1)
    cur.execute("drop table if exists tdb")
    cur.close()
    conn.close()


def test_ten_thread():
    con_str = getConStr()
    conn = _connect(con_str)
    cur = conn.cursor()
    cur.execute("drop table if exists tdb")
    cur.execute(DDL_TDB)
    cur.close()
    conn.close()

    thrs = []
    print("starting ten thread for insert operation at:", ctime())
    for i in range(10):
        t = MyThread("insert")
        thrs.append(t)
    for n in range(10):
        thrs[n].start()
    for j in range(10):
        thrs[j].join()
    print("end insert!")

    print("starting ten thread for select operation at:", ctime())
    thrs1 = []
    for i in range(10):
        t1 = MyThread("select")
        thrs1.append(t1)
    for n in range(10):
        thrs1[n].start()
    for j in range(10):
        thrs1[j].join()
    print("end select!")

    print("starting ten thread for update operation at:", ctime())
    thrs2 = []
    for i in range(10):
        t2 = MyThread("update")
        thrs2.append(t2)
    for n in range(10):
        thrs2[n].start()
    for j in range(10):
        thrs2[j].join()
    print("end update!")

    print("starting ten thread for delete operation at:", ctime())
    thrs3 = []
    for i in range(10):
        t3 = MyThread("delete")
        thrs3.append(t3)
    for n in range(10):
        thrs3[n].start()
    for j in range(10):
        thrs3[j].join()
    print("end delete!")

    conn = _connect(con_str)
    cur = conn.cursor()
    cur.execute("drop table if exists tdb")
    cur.close()
    conn.close()


if __name__ == "__main__":
    test_one_thread()
    test_ten_thread()
