import unittest
import ast
import pyodbc

import time
import locale
from xml.dom import minidom


def _set_from_result(result):
    if isinstance(result, (set, list, tuple)):
        return set(str(x) for x in result)
    if isinstance(result, str):
        try:
            parsed = ast.literal_eval(result)
            return set(str(x) for x in parsed)
        except (ValueError, SyntaxError):
            # CUBRID 형식: "{a, b, c, d}" (따옴표 없음)
            s = result.strip().strip('{}')
            if not s:
                return set()
            parts = [p.strip().strip('"\'') for p in s.split(',')]
            return set(parts)
    return set(str(x) for x in result)


class SetTest(unittest.TestCase):
    def getConStr(self):
        xmlt = minidom.parse('configuration/python_config.xml')
        ips = xmlt.childNodes[0].getElementsByTagName('ip')
        ip = ips[0].childNodes[0].toxml()
        ports = xmlt.childNodes[0].getElementsByTagName('port')
        port = ports[0].childNodes[0].toxml()
        dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
        dbname = dbnames[0].childNodes[0].toxml()
        conStr = "DRIVER={CUBRID ODBC Driver};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
        return conStr

    def setUp(self):
        conStr = self.getConStr()
        self.con = pyodbc.connect(self.getConStr())
        self.cur = self.con.cursor()

    def tearDown(self):
        self.cur.close()
        self.con.close()

    def test_escape_string(self):
        '''test escape string in pyodbc is not supported'''
        # try:
        #     print(pyodbc.escape_string('',1,1))
        # except Exception as e:
        #     errorValue=str(e)
        #     print("errorValue: ",errorValue)
        #     self.assertEqual(pyodbc.escape_string("cubrid \ Laptop",1),"cubrid \ Laptop")
        #     self.assertEqual(pyodbc.escape_string("cubrid \ Laptop",0),"cubrid \\\\ Laptop")

        # try:
        #     print(self.con.escape_string('',1,1))
        # except Exception as e:
        #     errorValue=str(e)
        #     print("errorValue: ",errorValue)
        #     self.assertEqual(self.con.escape_string("cubrid \ Laptop"),"cubrid \ Laptop")
        pass

    def test_row_to_dict(self):
        """test row to dict"""
        con = pyodbc.connect(self.getConStr())
        c = con.cursor()
        try:
            c.execute("drop table if exists cubrid_test")
            c.execute("create table cubrid_test(id integer auto_increment)")
            c.execute("insert into cubrid_test (id) values (?)", (1,))
            c.execute("select * from cubrid_test")

            row = c.fetchone()
            self.assertIsNotNone(row)
            self.assertEqual(row[0], 1)
            self.assertEqual(row.id, 1)
        finally:
            c.close()
            con.close()

    def test_set_prepare_int(self):
        "test_set_prepare_int"
        self.cur = self.con.cursor()
        self.con.autocommit = True

        # set(int) 두 컬럼
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(int), col_2 set(int))")
        self.cur.execute("insert into set_tbl values ({?,?,?,?}, {?,?,?})",
                         ('1', '2', '3', '4', '5', '6', '7'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'1', '2', '3', '4'})
        self.assertEqual(_set_from_result(data[1]), {'5', '6', '7'})

        # set(char), set(int) 혼합
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(char), col_2 set(int))")
        self.cur.execute("insert into set_tbl values ({?,?,?,?}, {?,?,?})",
                         ('a', 'b', 'c', 'd', '5', '6', '7'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'a', 'b', 'c', 'd'})
        self.assertEqual(_set_from_result(data[1]), {'5', '6', '7'})

    def test_set_prepare_char(self):
        "test_set_prepare_char"
        self.cur = self.con.cursor()
        self.con.autocommit = True

        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(CHAR) )")
        self.cur.execute("insert into set_tbl values ({?,?,?,?})", ('a', 'b', 'c', 'd'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'a', 'b', 'c', 'd'})

        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(CHAR) )")
        self.cur.execute("insert into set_tbl values ({?,?})", ('h', 'j'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'h', 'j'})

    def test_set_prepare_string(self):
        "test_set_prepare_string"
        self.cur = self.con.cursor()
        self.con.autocommit = True

        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(varchar) )")
        self.cur.execute("insert into set_tbl values ({?,?,?,?})",
                         ('abc', 'bcd', 'ceee', 'dddddd'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'abc', 'bcd', 'ceee', 'dddddd'})

    def test_set_prepare_combine(self):
        "test_set_prepare_combine"
        self.cur = self.con.cursor()
        self.con.autocommit = True

        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(varchar), col_2 set(int) )")
        self.cur.execute("insert into set_tbl values ({?,?}, {?,?,?})",
                         ('abc', 'def', '1', '23', '48'))

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(_set_from_result(data[0]), {'abc', 'def'})
        self.assertEqual(_set_from_result(data[1]), {'1', '23', '48'})

    def test_set_bit(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value = (b'\x00\x00',)
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl (col_1 set(bit(16)) )")
        self.cur.execute("insert into set_tbl values ({?})", value)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertIsNotNone(data)
        self.assertIn('0000', data[0])

if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(SetTest)
    unittest.TextTestRunner(verbosity=2).run(suite)
