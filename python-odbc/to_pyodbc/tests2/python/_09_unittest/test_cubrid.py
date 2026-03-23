import pyodbc
import unittest

from pyodbc import *
import time
import sys
import os
import os.path
from xml.dom import minidom


class CursorWrapper:
    def __init__(self, cursor):
        self.cursor = cursor
        self._sql = None
        self._params = {}
        self.rowcount = -1

    def prepare(self, sql):
        self._sql = sql
        self._params = {}

    def bind_param(self, index, value):
        self._params[index] = value
        
    def bind_lob(self, index, value):
        self._params[index] = value

    def execute(self, sql=None, params=None):
        try:
            if sql:
                self.cursor.execute(sql, params or ())
            else:
                if not self._sql:
                    raise Exception("No SQL prepared")
                if self._params:
                    max_idx = max(self._params.keys())
                    p_list = [self._params.get(i) for i in range(1, max_idx + 1)]
                    self.cursor.execute(self._sql, p_list)
                else:
                    self.cursor.execute(self._sql)
            
            if hasattr(self.cursor, 'rowcount'):
                self.rowcount = self.cursor.rowcount
            return self.cursor
        except Exception as e:
            raise e

    def __getattr__(self, name):
        return getattr(self.cursor, name)

class ConnectionWrapper:
    def __init__(self, con):
        self.con = con
    
    def cursor(self):
        return CursorWrapper(self.con.cursor())
        
    def __getattr__(self, name):
        return getattr(self.con, name)

class DatabaseTest(unittest.TestCase):
    driver = pyodbc
    xmlt = minidom.parse('configuration/python_config.xml')
    ips = xmlt.childNodes[0].getElementsByTagName('ip')
    ip = ips[0].childNodes[0].toxml()
    ports = xmlt.childNodes[0].getElementsByTagName('port')
    port = ports[0].childNodes[0].toxml()
    dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
    dbname = dbnames[0].childNodes[0].toxml()
    conStr = "DRIVER={CUBRID_ODBC_Unicode};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname    
    connect_args = (conStr,)
    connect_kw_args = {}

    def setUp(self):
        #con = self._connect()
        #cursor=con.cursor()
        pass

    def tearDown(self):
        #cursor.execute("drop class if exists test_date")
        #con.close()
        #cursor.close()
        pass

    def _connect(self):
        try:
            return ConnectionWrapper(self.driver.connect(
                    *self.connect_args, **self.connect_kw_args
                    ))
        except AttributeError:
            self.fail("No connect method found in self.driver module")
            
    def test_connect(self):
        con = self._connect()
        con.close()

    def test_server_version(self):
        """pyodbc with cubrid-odbc is not support: Connection.server_version() does not exist in pyodbc."""
        pass

    def test_client_version(self):
        """pyodbc with cubrid-odbc is not support: Connection.client_version() does not exist in pyodbc."""
        pass

    def test_Exceptions(self):
        # Make sure required exceptions exist, and are in the
        # defined heirarchy.
        self.assertTrue(
                issubclass(self.driver.InterfaceError, self.driver.Error)
                )
        self.assertTrue(
                issubclass(self.driver.DatabaseError,self.driver.Error)
                )
        self.assertTrue(
                issubclass(self.driver.NotSupportedError,self.driver.Error)
                )


    def test_commit(self):
        con = self._connect()
        try:
            # Commit must work, even if it doesn't do anything
            con.commit()
        finally:
            con.close()

    def test_rollback(self):
        con = self._connect()
        try:
            con.rollback()
        finally:
            con.close()

    def test_cursor(self):
        con = self._connect()
        try:
            cur = con.cursor()
        finally:
            cur.close()
            con.close()

    def test_cursor_isolation(self):
        con = self._connect()
        try:
            # Make sure cursors created from the same connection have
            # the documented transaction isolation level
            cur1 = con.cursor()
            cur2 = con.cursor()
            cur1.prepare("drop table if exists testpyodbc")
            cur1.execute()
            cur1.prepare('create table testpyodbc (name varchar(20))')
            cur1.execute()
            cur1.prepare("insert into testpyodbc values ('Blair')")
            cur1.execute()
            self.assertIn(cur1.rowcount, (-1, 1))
            cur2.prepare('select * from testpyodbc')
            cur2.execute()
            rows = cur2.fetchall()
            self.assertEqual(len(rows), 1)
        finally:
            con.close()

    def test_description(self):
        con = self._connect();
        try:
            cur = con.cursor()
            cur.prepare("drop table if exists testpyodbc")
            cur.execute()
            cur.prepare("create table testpyodbc (name varchar(20))")
            cur.execute()
            self.assertEqual(cur.description, None,
                    'cursor.description should be none after executing a '
                    'statement that can return no rows (such as create)')
            cur.prepare("select name from testpyodbc")
            cur.execute()
            self.assertEqual(len(cur.description), 1,
                    'cursor.description describes too many columns')
            self.assertEqual(len(cur.description[0]), 7,
                    'cursor.description[x] must have 7 elements (DB-API)')
            self.assertEqual(cur.description[0][0].lower(), 'name',
                    'cursor.description[x][0] must return column name')
            cur.close()
        finally:
            con.close()


    def test_rowcount(self):
        con = self._connect()
        try:
            cur = con.cursor()
            cur.prepare("drop table if exists testpyodbc")
            cur.execute()
            cur.prepare("create table testpyodbc (name varchar(20))")
            cur.execute()
            self.assertIn(cur.rowcount, (-1, 0),
                    'cursor.rowcount should be -1 or 0 after executing '
                    'no-result statements')
            cur.prepare("insert into testpyodbc values ('Blair')")
            cur.execute()
            self.assertIn(cur.rowcount, (-1, 1),
                    'cursor.rowcount should == number or rows inserted, or '
                    'set to -1 after executing an insert statment')
            cur.prepare("select name from testpyodbc")
            cur.execute()
            self.assertIn(cur.rowcount, (-1, 1),
                    'cursor.rowcount should == number of rows returned, or '
                    'set to -1 after executing a select statement')
            cur.close()
        finally:
            con.close()

    def test_isolation_level(self):
        """pyodbc with cubrid-odbc is not support: CUBRID_REP_CLASS_COMMIT_INSTANCE and set_isolation_level are CUBRID-specific."""
        pass
        
    def test_autocommit(self):
        con = self._connect()
        try:
            # pyodbc default is False (unlike CUBRID which defaults to True)
            self.assertEqual(con.autocommit, False,
                    'connection.autocommit default is FALSE in pyodbc')
            con.autocommit = True
            self.assertEqual(con.autocommit, True,
                    'connection.autocommit should TRUE after set on')
            con.autocommit = False
            self.assertEqual(con.autocommit, False,
                    'connection.autocommit should FALSE after set off')
        finally:
            con.close()

    def test_ping(self):
        """pyodbc with cubrid-odbc is not support: Connection.ping() does not exist in pyodbc."""
        pass

    def test_schema_info(self):
        """pyodbc with cubrid-odbc is not support: Connection.schema_info() is CUBRID-specific."""
        pass

    def test_insert_id(self):
        """pyodbc with cubrid-odbc is not support: Connection.insert_id() and Cursor.fetch_row() are CUBRID-specific."""
        pass

    samples = [
        'Carlton Cold',
        'Carlton Draft',
        'Mountain Goat',
        'Redback',
        'Victoria Bitter',
        'XXXX'
        ]

    def _prepare_data(self, cursor):
        cursor.prepare("insert into testpyodbc values (?),(?),(?),(?),(?),(?)")
        for i in range(len(self.samples)):
            cursor.bind_param(i+1, self.samples[i])
        cursor.execute()

    def _select_data(self, cursor):
        cursor.prepare("select * from testpyodbc")
        cursor.execute()

    def test_affected_rows(self):
        t_affected_rows = 'create table testpyodbc (name varchar(20))'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists testpyodbc")
            cur.execute()
            cur.prepare(t_affected_rows)
            cur.execute()
            self._prepare_data(cur)
            self.assertIn(cur.rowcount, (-1, 6))
        finally:
            cur.close()
            con.close()

    def test_data_seek(self):
        """pyodbc with cubrid-odbc is not support: Cursor.data_seek(), row_tell(), num_fields(), num_rows() are CUBRID-specific."""
        pass

    def test_row_seek(self):
        """pyodbc with cubrid-odbc is not support: Cursor.data_seek(), row_seek(), row_tell() are CUBRID-specific."""
        pass
   
    def test_bind_int(self):
        t_bind_int = 'create table test_int (id int)'
        samples_int = ['100', '200', '300', '400']
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists test_int")
            cur.execute()
            cur.prepare(t_bind_int);
            cur.execute()
            cur.prepare("insert into test_int values (?),(?),(?),(?)")
            for i in range(len(samples_int)):
                cur.bind_param(i+1, samples_int[i])
            cur.execute()
            self.assertIn(cur.rowcount, (-1, 4))
        finally:
            cur.close()
            con.close()

    def test_bind_float(self):
        ddl_float = 'create table test_float (id float)'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists test_float")
            cur.execute()
            cur.prepare(ddl_float)
            cur.execute()            
            cur.prepare("insert into test_float values (?)")
            cur.bind_param(1, '3.14')
            cur.execute()
            self.assertIn(cur.rowcount, (-1, 1))
        finally:
            cur.close()
            con.close()

    def test_bind_date_e(self):
        """pyodbc with cubrid-odbc is not support: ODBC driver returns generic error instead of CUBRID -494 for invalid date."""
        pass

    def test_bind_date(self):
        ddl_date = 'create table test_date (birthday date)'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists test_date")
            cur.execute()
            cur.prepare(ddl_date)
            cur.execute()
            cur.prepare('insert into test_date values (?),(?)')
            cur.bind_param(1, '12/25/2008')
            cur.bind_param(2, '2008-12-25')
            cur.execute()
        finally:
            cur.close()
            con.close()

    def test_bind_time(self):
        ddl_date = 'create table test_date (lunch time)'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists test_date")
            cur.execute()
            cur.prepare(ddl_date)
            cur.execute()
            cur.prepare('insert into test_date values (?),(?)')
            cur.bind_param(1, '13:10:30')
            cur.bind_param(2, '3:10:36')
            cur.execute()
        finally:
            cur.close()
            con.close()

    def test_bind_timestamp(self):
        ddl_date = 'create table test_date (lunch timestamp)'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.prepare("drop table if exists test_date")
            cur.execute()
            cur.prepare(ddl_date)
            cur.execute()
            cur.prepare('insert into test_date values (?),(?)')
            cur.bind_param(1, '12:00:00 AM 10/31/2011' )
            cur.bind_param(2, '1:15:45 PM 10/31/2008')
            cur.execute()
        finally:
            cur.close()
            con.close()

    def test_lob_file(self):
        """pyodbc with cubrid-odbc is not support: Connection.lob(), Cursor.bind_lob(), fetch_lob() are CUBRID-specific."""
        pass

    def test_lob_string(self):
        """pyodbc with cubrid-odbc is not support: Connection.lob(), Cursor.bind_lob(), fetch_lob() are CUBRID-specific."""
        pass

    def test_result_info(self):
        """pyodbc with cubrid-odbc is not support: Cursor.result_info() is CUBRID-specific."""
        pass
    
def suite():
    suite = unittest.TestSuite()
    suite.addTest(DatabaseTest("test_bind_timestamp"))
    return suite

if __name__ == '__main__':
    #unittest.main(defaultTest = 'suite')
    #unittest.main()
    #suite = unittest.TestLoader().loadTestsFromTestCase(DatabaseTest)
    #unittest.TextTestRunner(verbosity=2).run(suite)
    suite = unittest.TestSuite()
    if len(sys.argv) == 1:
        suite = unittest.TestLoader().loadTestsFromTestCase(DatabaseTest)
    else:
        for test_name in sys.argv[1:]:
            suite.addTest(DatabaseTest(test_name))
    unittest.TextTestRunner(verbosity=2).run(suite)
