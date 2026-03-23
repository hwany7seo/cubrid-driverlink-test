import pyodbc
import unittest

from pyodbc import *
import time
from xml.dom import minidom

class CubridTest(unittest.TestCase):
#this method contain pyodbc_ConnectionObject_repr, pyodbc_ConnectionObject_repr, next_result three methods
    def setUp(self):
        pass

    def tearDown(self):
        pass

    def test_nextresult(self):
        xmlt = minidom.parse('configuration/python_config.xml')
        ips = xmlt.childNodes[0].getElementsByTagName('ip')
        ip = ips[0].childNodes[0].toxml()
        ports = xmlt.childNodes[0].getElementsByTagName('port')
        port = ports[0].childNodes[0].toxml()
        dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
        dbname = dbnames[0].childNodes[0].toxml()
        conStr = "DRIVER={CUBRID_ODBC_Unicode};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
        con = pyodbc.connect(conStr)
        cur=con.cursor()
        try:
            print(con)
            print(cur)
            cur.execute("drop table if exists nextResult_tb")
            cur.execute("create table nextResult_tb(id int)")
            cur.execute("insert into nextResult_tb values(1),(2),(3),(4),(5)")
            cur.execute("drop table if exists nextResult_tb2")
            cur.execute("create table nextResult_tb2(id int)")
            cur.execute("insert into nextResult_tb2 values(6),(7),(8),(9),(10)")
            cur.execute("select * from nextResult_tb;select * from nextResult_tb2")
            cur.nextset()
            row=cur.fetchone()
            while row:
                print ("row value: ", row)
                row=cur.fetchone() 

        except Exception as e:
            errorValue=str(e)
            print("errorValue: ",errorValue)
            self.fail(f"Test failed with exception: {errorValue}")
        finally:
            cur.close()
            con.close()   
            print(cur)
            print(con)

if __name__ == '__main__':
    #unittest.main(defaultTest = 'suite')
    #unittest.main()
    suite = unittest.TestLoader().loadTestsFromTestCase(CubridTest)
    unittest.TextTestRunner(verbosity=2).run(suite)

