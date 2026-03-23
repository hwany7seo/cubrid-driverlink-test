import pyodbc
import unittest

from pyodbc import *
import time
from xml.dom import minidom

class CubridTest(unittest.TestCase):
    def setUp(self):
        #con = self._connect()
        #cursor=con.cursor()
        pass

    def tearDown(self):
        #cursor.execute("drop class if exists test_date")
        #con.close()
        #cursor.close()
        pass

    def test_lob(self):
        xmlt = minidom.parse('configuration/python_config.xml')
        ips = xmlt.childNodes[0].getElementsByTagName('ip')
        ip = ips[0].childNodes[0].toxml()
        ports = xmlt.childNodes[0].getElementsByTagName('port')
        port = ports[0].childNodes[0].toxml()
        dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
        dbname = dbnames[0].childNodes[0].toxml()
        conStr = "DRIVER={CUBRID_ODBC_Unicode};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
        con = pyodbc.connect(conStr)
        cur = con.cursor()
        
        try:
            cur.execute("drop table if exists lob_tb")
            cur.execute("create table lob_tb(image_id int PRIMARY KEY AUTO_INCREMENT, image BLOB)")
           
            try:
                with open('cubrid_logo.png', 'rb') as f:
                    img_data = f.read()
            except FileNotFoundError:
                img_data = b'dummy image data for testing'

            try:
            #    SQL_BLOB = -107
            #    cur.setinputsizes([(SQL_BLOB, len(img_data), 0)]) // not supported in CUBRID_ODBC_Unicode
                cur.execute("insert into lob_tb (image) values (?)", (pyodbc.Binary(img_data),))
                con.commit()
                
                cur.execute("select image from lob_tb")
                row = cur.fetchone()
                self.assertIsNotNone(row)
                self.assertEqual(row[0], img_data)
                print("img_data: ", img_data)
                print("row[0]: ", row[0])
            except pyodbc.Error as pe:
                print("LOB binding not supported or failed:", pe)
                self.fail(f"Test failed with exception: {pe}")
           
        except Exception as e:
              errorValue = str(e)
              print("errorValue: ", errorValue)
              self.fail(f"Test failed with exception: {errorValue}")
        finally:
           cur.close()
           con.close()

if __name__ == '__main__':
    #unittest.main(defaultTest = 'suite')
    #unittest.main()
    suite = unittest.TestLoader().loadTestsFromTestCase(CubridTest)
    unittest.TextTestRunner(verbosity=2).run(suite)

