import unittest
import pyodbc
import time
import locale
from xml.dom import minidom
from decimal import Decimal

class FetchmanyTest(unittest.TestCase):
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
                self.cur = self.con.cursor()

                sqlDrop = "drop table if exists tdb"
                self.cur.execute(sqlDrop)
                sqlCreate = "create table tdb(id NUMERIC AUTO_INCREMENT(1, 1), age int, name varchar(50))"
                self.cur.execute(sqlCreate)
                self.rownum = 1000
                for i in range(self.rownum):
                        sqlInsert = "insert into tdb values(1,20,'myName')"
                        self.cur.execute(sqlInsert)

        def tearDown(self):
                sqlDrop = "drop table if exists tdb"
                self.cur.execute(sqlDrop)
                self.cur.close()
                self.con.close()

        def test_fetchmany_nosize(self):
#               test fetchmany without inputing size
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchmany()
                self.assertEqual(1, len(data))
                # dataCheck=[1,20,'myName']
                dataCheck = (Decimal('1'), 20, 'myName') 
                self.assertEqual(dataCheck, tuple(data[0]))

        def test_fetchmany_negativeOne(self):
                # pyodbc and cubrid-python driver behavior is different
                # pyodbc will return 1000 records
                # cubrid-python driver will return 0 records
#               test fetchmany with size = -1
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchmany(-1)
                self.assertEqual(1000, len(data))

        def test_fetchmany_zero(self):
#               test fetchmany with size = 0
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchmany(0)
                self.assertEqual(0, len(data))

        def test_fetchmany_all(self):
#               test fetchmany with size = self.rownum
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchmany(self.rownum)
                self.assertEqual(self.rownum, len(data))

        def test_fetchmany_overflow(self):
#               test fetchmany with size = self.rownum + 10
                sqlSelect = "select * from tdb"
                self.cur.execute(sqlSelect)
                data = self.cur.fetchmany(self.rownum+10)
                self.assertEqual(self.rownum, len(data))



if __name__ == '__main__':
        suite = unittest.TestLoader().loadTestsFromTestCase(FetchmanyTest)
        unittest.TextTestRunner(verbosity=2).run(suite)