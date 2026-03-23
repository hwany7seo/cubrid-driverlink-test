import os
import unittest
import pyodbc
from xml.dom import minidom


def _config_xml_path():
    # tests2/python/_03_fetchone/ -> tests2/configuration/
    return os.path.normpath(
        os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "..", "configuration", "python_config.xml")
    )


def _xml_text(elem):
    if elem is None or elem.firstChild is None:
        return ""
    n = elem.firstChild
    if n.nodeType == n.TEXT_NODE:
        return n.data.strip()
    return (n.toxml() if hasattr(n, "toxml") else str(n)).strip()


def get_con_str():
    root = minidom.parse(_config_xml_path()).documentElement
    ip = _xml_text(root.getElementsByTagName("ip")[0])
    port = _xml_text(root.getElementsByTagName("port")[0])
    dbname = _xml_text(root.getElementsByTagName("dbname")[0])
    return (
        "DRIVER={CUBRID_ODBC_Unicode};SERVER="
        + ip
        + ";PORT="
        + port
        + ";UID=dba;PWD=;DB_NAME="
        + dbname
    )


class FetchoneTest(unittest.TestCase):
    """
    One ODBC connection per class.

    unittest runs test_InvalidConn before test_fetchone_* (name sort: I < f).
    The first test's setUp used to open a connection successfully; later tests
    called setUp again and some CUBRID broker/driver builds reject the second
    pyodbc.connect in quick succession with HY000 and no message.
    """

    @classmethod
    def setUpClass(cls):
        cls.con = pyodbc.connect(get_con_str(), timeout=30)
        cls.cur = cls.con.cursor()

    @classmethod
    def tearDownClass(cls):
        try:
            cls.cur.close()
        except Exception:
            pass
        try:
            cls.con.close()
        except Exception:
            pass

    def setUp(self):
        self.con = self.__class__.con
        self.cur = self.__class__.cur
        sql_drop = "drop table if exists tdb"
        self.cur.execute(sql_drop)
        sql_create = "create table tdb(id NUMERIC AUTO_INCREMENT(1, 1), age int, name varchar(50))"
        self.cur.execute(sql_create)
        sql_insert = "insert into tdb values (null,20,'Lily')"
        self.cur.execute(sql_insert)

    def tearDown(self):
        sql_drop = "drop table if exists tdb"
        self.cur.execute(sql_drop)

    def test_fetchone_multi(self):
        sql_insert = "insert into tdb(age) values(21)"
        self.cur.execute(sql_insert)
        sql_select = "select * from tdb"
        self.cur.execute(sql_select)
        data1 = self.cur.fetchone()
        data2 = self.cur.fetchone()
        self.assertEqual(20, data1[1])
        self.assertEqual(21, data2[1])

    def test_fetchone_largeData(self):
        data_num = 10000
        for i in range(data_num):
            sql_insert = "insert into tdb values(NULL,21,'myName')"
            self.cur.execute(sql_insert)
        sql_select = "select * from tdb order by id asc"
        self.cur.execute(sql_select)
        for i in range(data_num):
            data = self.cur.fetchone()
            self.assertEqual(i + 1, data[0])

    def test_fetchone_norecord(self):
        sql_delete = "delete from tdb"
        self.cur.execute(sql_delete)
        sql_select = "select * from tdb"
        self.cur.execute(sql_select)
        data = self.cur.fetchone()
        self.assertEqual(None, data)

    def test_fetchone_overflow(self):
        sql_select = "select * from tdb"
        self.cur.execute(sql_select)
        data1 = self.cur.fetchone()
        data2 = self.cur.fetchone()
        self.assertEqual(None, data2)

    def test_InvalidConn(self):
        try:
            con1 = pyodbc.connect(
                "DRIVER={CUBRID_ODBC_Unicode};SERVER=test-db-server;PORT=33000;UID=dba;PWD=;DB_NAME=invalid_db"
            )
            con1.close()
        except pyodbc.Error as e:
            self.assertEqual("HY000", e.args[0])
        else:
            self.fail("connection should not be established")


if __name__ == "__main__":
    suite = unittest.TestLoader().loadTestsFromTestCase(FetchoneTest)
    unittest.TextTestRunner(verbosity=2).run(suite)
