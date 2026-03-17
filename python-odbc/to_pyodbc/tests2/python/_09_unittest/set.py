import unittest
import pyodbc

import time
import locale
from xml.dom import minidom

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
        self.con = pyodbc.connect('DRIVER={CUBRID ODBC Driver};SERVER=192.168.2.32;PORT=33000;UID=dba;PWD=;DB_NAME=demodb')
        self.cur = self.con.cursor()

    def tearDown(self):
        self.cur.close()
        self.con.close()

    def test_escape_string(self):
        '''test escape string'''
        try:
            print(pyodbc.escape_string('',1,1))
        except Exception as e:
            errorValue=str(e)
            print("errorValue: ",errorValue)
        self.assertEqual(pyodbc.escape_string("cubrid \ Laptop",1),"cubrid \ Laptop")
        self.assertEqual(pyodbc.escape_string("cubrid \ Laptop",0),"cubrid \\\\ Laptop")

        try:
            print(self.con.escape_string('',1,1))
        except Exception as e:
            errorValue=str(e)
            print("errorValue: ",errorValue)
        self.assertEqual(self.con.escape_string("cubrid \ Laptop"),"cubrid \ Laptop")
    def test_row_to_dict(self):
        con = pyodbc.connect('DRIVER={CUBRID ODBC Driver};SERVER=192.168.2.32;PORT=33000;UID=dba;PWD=;DB_NAME=demodb')
        c = con.cursor()

        c.prepare("DROP TABLE IF EXISTS cubrid_test")
        c.execute()

        c.prepare("CREATE TABLE cubrid_test(id integer auto_increment);")
        c.execute()

        c.prepare('insert into cubrid_test (id) values (1)')
        c.execute()

        c.prepare("select * from cubrid_test");
        c.execute()

        row = c.fetch_row(1)
        print('\nrow:\n')
        print(row)
    def test_set_prepare_int(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value =  [('1','2','3','4'),('5', '6', '7')]
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(int), col_2 set(int))")
        self.cur.execute("insert into set_tbl values (?, ?)", value)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['1', '2', '3', '4'])

        value =  [('a','b','c','d'),('5', '6', '7')]
        etype = (1,8)
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(char), col_2 set(int))")
        self.cur.execute("insert into set_tbl values (?, ?)", value, etype)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['a', 'b', 'c', 'd'])
        self.assertEqual(data[1],['5', '6', '7'])

    def test_set_prepare_char(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value =  [('a','b','c','d')]
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(CHAR) )")
        self.cur.execute("insert into set_tbl values (?)", value)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['a', 'b', 'c', 'd'])

        value =  [('h','j')]
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(CHAR) )")
        self.cur.execute("insert into set_tbl values (?)", value, 1)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['h', 'j'])

    def test_set_prepare_string(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value =  [('abc','bcd','ceee','dddddd')]
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(varchar) )")
        self.cur.execute("insert into set_tbl values (?)", value)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['abc', 'bcd', 'ceee', 'dddddd'])

    def test_set_prepare_combine(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value =  [('abc','def'),('1','23','48')]
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl ( col_1 set(varchar),col_2 set(int) )")
        self.cur.execute("insert into set_tbl values (?,?)", value)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['abc', 'def'])
        self.assertEqual(data[1],['1', '23', '48'])
    def test_set_bit(self):
        self.cur = self.con.cursor()
        self.con.autocommit = True

        value=(('0','0'),)
        etype = (pyodbc.FIELD_TYPE.BIT)
        self.cur.execute("DROP TABLE IF EXISTS set_tbl")
        self.cur.execute("CREATE TABLE set_tbl (col_1 set(bit(16)) )")
        self.cur.execute("insert into set_tbl values (?)", value,etype)

        self.cur.execute("SELECT * FROM set_tbl")
        data = self.cur.fetchone()
        self.assertEqual(data[0],['0000'])

if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(SetTest)
    unittest.TextTestRunner(verbosity=2).run(suite)
