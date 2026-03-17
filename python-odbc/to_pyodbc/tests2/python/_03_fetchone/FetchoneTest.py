import unittest
import pyodbc
import time
import locale
from xml.dom import minidom

class FetchoneTest(unittest.TestCase):
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
                self.con = pyodbc.connect(conStr)
#                self.con = pyodbc.connect('DRIVER={CUBRID ODBC Driver};SERVER=192.168.2.32;PORT=33000;UID=dba;PWD=;DB_NAME=demodb')
                self.cur = self.con.cursor()

                sqlDrop = "drop table if exists tdb"
                self.cur.execute(sqlDrop)
                sqlCreate = "create table tdb(id NUMERIC AUTO_INCREMENT(1, 1), age int, name varchar(50))"
                self.cur.execute(sqlCreate)
                sqlInsert = "insert into tdb values (null,20,'Lily')"
                self.cur.execute(sqlInsert)

        def tearDown(self):
                sqlDrop = "drop table if exists tdb"
                self.cur.execute(sqlDrop)
                self.cur.close()
                self.con.close()

        def test_fetchone_multi(self):
#               test fetchone more than one time
                sqlInsert = "insert into tdb(age) values(21)"
                self.cur.execute(sqlInsert)
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data1 = self.cur.fetchone()
                data2 = self.cur.fetchone()
                self.assertEqual(20, data1[1])
                self.assertEqual(21, data2[1])

        def test_fetchone_largeData(self):
#               test fetchone with 10000 records
                dataNum=10000
                for i in range(dataNum):
                        sqlInsert = "insert into tdb values(NULL,21,'myName')"
                        self.cur.execute(sqlInsert)
                sqlSelect = "select * from tdb order by id asc"
                self.cur.execute(sqlSelect)
                for i in range(dataNum):
                        data = self.cur.fetchone()
#                         self.assertEqual(i+1, locale.atoi(data[0]))
                        self.assertEqual(i+1, data[0])

        def test_fetchone_norecord(self):
#               test fetchone when there has no record in table
                sqlDelete = "delete from tdb"
                self.cur.execute(sqlDelete)
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchone()
                self.assertEqual(None,data)

        def test_fetchone_overflow(self):
#               test fetchone when overflow
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data1 = self.cur.fetchone()
                data2 = self.cur.fetchone()
                self.assertEqual(None,data2)

        def test_InvalidConn(self):
                try:
                        self.con1 = pyodbc.connect('DRIVER={CUBRID ODBC Driver};SERVER=192.168.2.32;PORT=33000;UID=dba;PWD=;DB_NAME=invalid_db')
                        self.con1.close()
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])
                else:
                        self.fail("connection should not be established")


if __name__ == '__main__':
        suite = unittest.TestLoader().loadTestsFromTestCase(FetchoneTest)
        unittest.TextTestRunner(verbosity=2).run(suite)
