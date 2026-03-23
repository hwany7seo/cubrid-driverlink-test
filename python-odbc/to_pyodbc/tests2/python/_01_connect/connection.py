import pyodbc
import unittest
import os
import sys
from xml.dom import minidom

class CUBRIDPythonDBITest(unittest.TestCase):

        def setup(self):
                pass
        def tearDown(self):
                pass
        def getConStr(self):
                xmlt = minidom.parse('configuration/python_config.xml')
                ips = xmlt.childNodes[0].getElementsByTagName('ip')
                ip = ips[0].childNodes[0].toxml()
                ports = xmlt.childNodes[0].getElementsByTagName('port')
                port = ports[0].childNodes[0].toxml()
                dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
                dbname = dbnames[0].childNodes[0].toxml()
                conStr = "DRIVER={CUBRID_ODBC_Unicode};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
                return conStr
        def test_connect(self):
                print("01. Common connection")
                conStr = self.getConStr()
                self.con=pyodbc.connect(conStr)
                self.c=self.con.cursor()
                self.c.execute("select * from db_class limit 5;")
                row=self.c.fetchone()
                print(row)
                self.c.close()
                self.con.close()
        def test_connect_withautocommit(self):
                print("\n02. Connection with autocommit property")
                conStr = self.getConStr()
                self.con=pyodbc.connect(conStr)
                self.c=self.con.cursor()
                self.c.execute("select * from db_class limit 5;")
                row=self.c.fetchone()
                print(row)
                self.c.close()
                self.con.close()
        def test_connection_with_wrong_parameter(self):
                print("\n03. Connection with wrong parameter")
                try:
                        conStr = self.getConStr()
                        self.con = pyodbc.connect(conStr)
                except Exception as e:
                        print("connect error: ", e)
                        #self.con.close()
        def test_connection_with_ip(self):
                print("\n04. Connection with other computer")
                conStr = self.getConStr()
                self.con=pyodbc.connect(conStr)
                self.c=self.con.cursor()
                self.c.execute("select * from db_class;")
                row=self.c.fetchone()
                print(row)
                self.c.close()
                self.con.close()
                        
        def test_connection_with_alhost(self):
                print("\n05. Connection with alhost")
                conStr = self.getConStr()
                self.con=pyodbc.connect(conStr)
                self.c=self.con.cursor()
                self.c.execute("select * from db_class;")
                row=self.c.fetchone()
                print(row)
                self.c.close()
                self.con.close()

if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(CUBRIDPythonDBITest)
    unittest.TextTestRunner(verbosity=2).run(suite)