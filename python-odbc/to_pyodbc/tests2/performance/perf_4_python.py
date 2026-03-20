import os
import threading
import time
import pyodbc
from time import ctime
from xml.dom import minidom


def _config_xml_path():
    return os.path.normpath(
        os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'configuration', 'python_config.xml')
    )


def getConStr():
    xmlt = minidom.parse(_config_xml_path())
    root = xmlt.documentElement
    ip = root.getElementsByTagName('ip')[0].firstChild.toxml()
    port = root.getElementsByTagName('port')[0].firstChild.toxml()
    dbname = root.getElementsByTagName('dbname')[0].firstChild.toxml()
    return (
        "DRIVER={CUBRID ODBC Driver};SERVER="
        + ip
        + ";PORT="
        + port
        + ";UID=dba;PWD=;DB_NAME="
        + dbname
    )


class MyThread(threading.Thread):
    def __init__(self, name=''):
        threading.Thread.__init__(self)
        self.name = name

    def run(self):
        con_str = getConStr()
        self.conn = pyodbc.connect(con_str)
        self.cur = self.conn.cursor()
        start_time = time.time()
        if self.name == 'insert':
            self.insert_test()
        elif self.name == 'delete':
            self.delete_test()
        elif self.name == 'update':
            self.update_test()
        elif self.name == 'select':
            self.select_test()
        end_time = time.time()
        elapse_time = end_time - start_time
        print(
            "**The operation - "
            + self.name
            + " ,total elapse time:"
            + '%f' % elapse_time
            + " sec."
        )
        self.cur.close()
        self.conn.close()

    def insert_test(self):
        print("start insert...")
        each_st_time = time.time()
        for n in range(10000000):
            sql = (
                "insert into tdb values("
                + str(n)
                + ", 'systimestamp +  pefor', systimestamp, "
                + str(n)
                + ")"
            )
            self.cur.execute(sql)
        each_ed_time = time.time()
        eslap_time = each_ed_time - each_st_time
        print(
            "**The operation is insert, and the elapse time for insert:"
            + '%f' % eslap_time
            + "sec."
        )

    def delete_test(self):
        print("start delete...")
        each_st_time = time.time()
        for n in range(10000000):
            if n == 0 or n == 1:
                limit_num = 1
            else:
                limit_num = n * 100
            sql = "delete from tdb where a<" + '%d' % limit_num
            print(sql)
            self.cur.execute(sql)
        each_ed_time = time.time()
        eslap_time = each_ed_time - each_st_time
        print(
            "**The operation is delete, and the elapse time for one commit:"
            + '%f' % eslap_time
            + "sec."
        )

    def update_test(self):
        print("start update...")
        each_st_time = time.time()
        for n in range(10000000):
            sql = "update tdb set e = e + 10000000 where a=" + '%d' % n
            self.cur.execute(sql)
        each_ed_time = time.time()
        eslap_time = each_ed_time - each_st_time
        print(
            "**The operation is update, and the elapse time for one commit:"
            + '%f' % eslap_time
            + "sec."
        )

    def select_test(self):
        print("start select...")
        each_st_time = time.time()
        for n in range(10000000):
            if n == 0 or n == 1:
                limit_num = 2
            else:
                limit_num = (n + 1) * 100
            sql = "select * from tdb where a <" + '%d' % limit_num
            self.cur.execute(sql)
        each_ed_time = time.time()
        eslap_time = each_ed_time - each_st_time
        print(
            "**The operation is select, and the elapse for each time:"
            + '%f' % eslap_time
            + "sec."
        )


def test_one_thread():
    con_str = getConStr()
    conn = pyodbc.connect(con_str)
    cur = conn.cursor()
    cur.execute('drop table if exists tdb')
    cur.execute('create table tdb(a int, b varchar(20), c timestamp, e int)')

    print('starting one thread operation at:', ctime())
    t1 = MyThread('insert')
    t1.start()
    t1.join()
    t1 = MyThread('select')
    t1.start()
    t1.join()
    t1 = MyThread('update')
    t1.start()
    t1.join()
    t1 = MyThread('delete')
    t1.start()
    t1.join()
    time.sleep(1)
    cur.execute('drop table if exists tdb')
    cur.close()
    conn.close()


def test_ten_thread():
    con_str = getConStr()
    conn = pyodbc.connect(con_str)
    cur = conn.cursor()
    cur.execute('drop table if exists tdb')
    cur.execute('create table tdb(a int, b varchar(20), c timestamp, e int)')

    thrs = []
    print('starting ten thread for insert operation at:', ctime())
    for i in range(10):
        t = MyThread('insert')
        thrs.append(t)
    for n in range(10):
        thrs[n].start()
    for j in range(10):
        thrs[j].join()
    print('end insert!')

    print('starting ten thread for select operation at:', ctime())
    thrs1 = []
    for i in range(10):
        t1 = MyThread('select')
        thrs1.append(t1)
    for n in range(10):
        thrs1[n].start()
    for j in range(10):
        thrs1[j].join()
    print('end select!')

    print('starting ten thread for update operation at:', ctime())
    thrs2 = []
    for i in range(10):
        t2 = MyThread('update')
        thrs2.append(t2)
    for n in range(10):
        thrs2[n].start()
    for j in range(10):
        thrs2[j].join()
    print('end update!')

    print('starting ten thread for delete operation at:', ctime())
    thrs3 = []
    for i in range(10):
        t3 = MyThread('delete')
        thrs3.append(t3)
    for n in range(10):
        thrs3[n].start()
    for j in range(10):
        thrs3[j].join()
    print('end delete!')

    cur.execute('drop table if exists tdb')
    cur.close()
    conn.close()


if __name__ == '__main__':
    test_one_thread()
    test_ten_thread()
