import pyodbc
import unittest

from pyodbc import *
import time
from xml.dom import minidom

class CubridTest(unittest.TestCase):
    def setUp(self):
        pass

    def tearDown(self):
        pass

    def _connect(self):
        xmlt = minidom.parse('configuration/python_config.xml')
        ips = xmlt.childNodes[0].getElementsByTagName('ip')
        ip = ips[0].childNodes[0].toxml()
        ports = xmlt.childNodes[0].getElementsByTagName('port')
        port = ports[0].childNodes[0].toxml()
        dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
        dbname = dbnames[0].childNodes[0].toxml()
        conStr = "DRIVER={CUBRID_ODBC_Unicode};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
        return pyodbc.connect(conStr)

    def test_lob(self):
        con = self._connect()
        cur = con.cursor()

        try:
            cur.execute("drop table if exists lob_tb")
            cur.execute("create table lob_tb(image_id int PRIMARY KEY AUTO_INCREMENT, image BLOB)")

            try:
                with open('cubrid_logo.png', 'rb') as f:
                    img_data = f.read()
            except FileNotFoundError:
                img_data = b'dummy image data for testing'

            cur.execute("insert into lob_tb (image) values (?)", (pyodbc.Binary(img_data),))
            con.commit()

            cur.execute("select image from lob_tb")
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertEqual(row[0], img_data)
        finally:
            cur.close()
            con.close()

    def test_clob_direct_select(self):
        """Regression: direct CLOB SELECT must work without CAST to VARCHAR."""
        con = self._connect()
        cur = con.cursor()
        text = 'hello world'

        try:
            cur.execute("drop table if exists lob_clob_tb")
            cur.execute("create table lob_clob_tb(content clob)")
            cur.execute("insert into lob_clob_tb values (?)", (text,))
            con.commit()

            cur.execute("select content from lob_clob_tb")
            row = cur.fetchone()
            self.assertIsNotNone(row)
            got = row[0]
            if isinstance(got, bytes):
                got = got.decode('utf-8')
            self.assertEqual(str(got).rstrip(), text)
        finally:
            cur.close()
            con.close()

    def test_set_scalar_param_bind(self):
        """Regression: SET column bind via single ? marker (native driver style)."""
        con = self._connect()
        cur = con.cursor()

        try:
            cur.execute("drop table if exists lob_set_tb")
            cur.execute("create table lob_set_tb(id int, s set(varchar))")
            cur.execute("insert into lob_set_tb values (1, ?)", ('{a,b}',))
            con.commit()

            cur.execute("select s from lob_set_tb where id=1")
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertIn('a', str(row[0]))
            self.assertIn('b', str(row[0]))
        finally:
            cur.close()
            con.close()

if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(CubridTest)
    unittest.TextTestRunner(verbosity=2).run(suite)
