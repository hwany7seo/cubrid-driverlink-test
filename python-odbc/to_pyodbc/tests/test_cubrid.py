# pylint: disable=missing-function-docstring,missing-class-docstring
import unittest
import os
from xml.dom import minidom

import pyodbc


def _cfg_text(doc, tag):
    nodes = doc.getElementsByTagName(tag)
    if not nodes or nodes[0].firstChild is None:
        return ''
    return nodes[0].firstChild.data.strip()


def _as_bytes(val):
    if val is None:
        return b''
    if isinstance(val, bytes):
        return val
    if isinstance(val, memoryview):
        return val.tobytes()
    if isinstance(val, bytearray):
        return bytes(val)
    return bytes(val)


# 1x1 PNG — used when cubrid_logo.png is absent (same pyodbc bytes bind path)
_MIN_PNG = (
    b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01'
    b'\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\nIDATx\x9cc\x00\x01'
    b'\x00\x00\x05\x00\x01\r\n-\xb4\x00\x00\x00\x00IEND\xaeB`\x82'
)

def _primary_key_column_names(cursor, table_name_lower):
    cursor.execute(
        """
        SELECT k.key_attr_name FROM db_index_key k
        INNER JOIN db_index i ON k.index_name = i.index_name
            AND k.class_name = i.class_name
        WHERE LOWER(i.class_name) = ? AND i.is_primary_key = 'YES'
        ORDER BY k.key_order
        """,
        (table_name_lower,),
    )
    return [str(r[0]).lower() for r in cursor.fetchall()]


class DatabaseTest(unittest.TestCase):
    driver = pyodbc

    xmlt = minidom.parse('python_config.xml')
    ip = _cfg_text(xmlt, 'ip')
    port = _cfg_text(xmlt, 'port')
    dbname = _cfg_text(xmlt, 'dbname')
    conStr = "DRIVER={CUBRID ODBC Driver};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname

    connect_args = (conStr,)
    connect_kw_args = {}

    def setUp(self):
        pass

    def tearDown(self):
        pass

    def _check_table_exist(self, connect):
        cursor = connect.cursor()
        cursor.execute('DROP TABLE IF EXISTS testpyodbc')
        connect.commit()
        cursor.close()

    def _connect(self):
        if not hasattr(self.driver, 'connect'):
            self.fail("No connect method found in self.driver module")
        con = self.driver.connect(*self.connect_args, **self.connect_kw_args)
        self._check_table_exist(con)
        return con

    def test_connect(self):
        con = self._connect()
        con.close()

    def test_server_version(self):
        con = self._connect()
        try:
            cur = con.cursor()
            cur.execute('SELECT VERSION()')
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertTrue(len(str(row[0])) > 0)
        finally:
            con.close()

    def test_client_version(self):
        con = self._connect()
        try:
            ver = con.getinfo(pyodbc.SQL_DRIVER_VER)
            self.assertIsInstance(ver, str)
            self.assertTrue(len(ver) > 0)
        finally:
            con.close()

    def test_Exceptions(self):
        self.assertTrue(
            issubclass(self.driver.InterfaceError, self.driver.Error)
        )
        self.assertTrue(
            issubclass(self.driver.DatabaseError, self.driver.Error)
        )
        self.assertTrue(
            issubclass(self.driver.NotSupportedError, self.driver.Error)
        )

    def test_commit(self):
        con = self._connect()
        try:
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
            cur.close()
        finally:
            con.close()

    def test_cursor_isolation(self):
        con = self._connect()
        try:
            cur1 = con.cursor()
            cur2 = con.cursor()
            cur1.execute('create table testpyodbc (name varchar(20))')
            cur1.execute("insert into testpyodbc values ('Blair')")
            self.assertTrue(cur1.rowcount in (-1, 1))
            cur2.execute('select * from testpyodbc')
            rows = cur2.fetchall()
            self.assertEqual(len(rows), 1)
            self.assertEqual(len(rows[0]), 1)
            self.assertEqual(tuple(rows[0]), ('Blair',))
        finally:
            con.close()

    def test_description(self):
        con = self._connect()
        try:
            cur = con.cursor()
            cur.execute("create table testpyodbc (name varchar(20))")
            self.assertIsNone(
                cur.description,
                'cursor.description should be none after DDL',
            )
            cur.execute('select name from testpyodbc')
            self.assertEqual(len(cur.description), 1)
            self.assertEqual(len(cur.description[0]), 7)
            self.assertEqual(cur.description[0][0].lower(), 'name')
            cur.close()
        finally:
            con.close()

    def test_rowcount(self):
        con = self._connect()
        try:
            cur = con.cursor()
            cur.execute("create table testpyodbc (name varchar(20))")
            self.assertTrue(cur.rowcount in (-1, 0))
            cur.execute("insert into testpyodbc values ('Blair')")
            self.assertTrue(cur.rowcount in (-1, 1))
            cur.execute('select name from testpyodbc')
            self.assertTrue(cur.rowcount in (-1, 1))
            cur.close()
        finally:
            con.close()


    @unittest.skip("pyodbc with cubrid-odbc is not support: CUBRID_REP_CLASS_COMMIT_INSTANCE and set_isolation_level are CUBRID-specific.")
    def test_isolation_level(self):
        pass

    def test_autocommit(self):
        con = self._connect()
        try:
            self.assertFalse(con.autocommit)
            con.autocommit = True
            self.assertTrue(con.autocommit)
            con.autocommit = False
            self.assertFalse(con.autocommit)
        finally:
            con.close()

    def test_ping(self):
        con = self._connect()
        try:
            if hasattr(con, 'ping'):
                self.assertEqual(con.ping(), 1)
            else:
                cur = con.cursor()
                cur.execute('SELECT 1')
                self.assertEqual(cur.fetchone()[0], 1)
        finally:
            con.close()

    def test_schema_info(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute(
                "SELECT class_name FROM db_class WHERE LOWER(class_name) = ?",
                ('db_class',),
            )
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertEqual(str(row[0]).lower(), 'db_class')
        finally:
            cur.close()
            con.close()

    def test_insert_id(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute(
                'create table testpyodbc (id numeric auto_increment(1000000000000, 2), '
                'name varchar(200))'
            )
            cur.execute("insert into testpyodbc (name) values (?)", ('Blair',))
            cur.execute('select last_insert_id()')
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertEqual(int(row[0]), 1000000000000)
        finally:
            cur.close()
            con.close()

    samples = [
        'Carlton Cold',
        'Carlton Draft',
        'Mountain Goat',
        'Redback',
        'Victoria Bitter',
        'XXXX',
    ]

    def test_affected_rows(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (name varchar(20))')
            cur.executemany(
                'insert into testpyodbc values (?)',
                [(s,) for s in self.samples],
            )
            self.assertTrue(cur.rowcount in (-1, 6))
        finally:
            cur.close()
            con.close()

    @unittest.skip('pyodbc.Cursor has no scroll(); CUBRID ODBC path has no DB-API scroll')
    def test_data_seek(self):
        pass

    @unittest.skip('pyodbc.Cursor has no scroll(); CUBRID ODBC path has no DB-API scroll')
    def test_row_seek(self):
        pass

    def test_bind_int(self):
        samples_int = ['100', '200', '300', '400']
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (id int)')
            cur.executemany(
                'insert into testpyodbc values (?)',
                [(x,) for x in samples_int],
            )
            self.assertTrue(cur.rowcount in (-1, 4))
        finally:
            cur.close()
            con.close()

    def test_bind_float(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (id float)')
            cur.execute('insert into testpyodbc values (?)', ('3.14',))
            self.assertTrue(cur.rowcount in (-1, 1))
        finally:
            cur.close()
            con.close()

    def test_bind_date_e(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (birthday date)')
            with self.assertRaises(pyodbc.Error):
                cur.execute('insert into testpyodbc values (?)', ('2011-2-31',))
        finally:
            cur.close()
            con.close()

    def test_bind_date(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (birthday date)')
            cur.execute('insert into testpyodbc values (?)', ('1987-10-29',))
            self.assertTrue(cur.rowcount in (-1, 1))
        finally:
            cur.close()
            con.close()

    def test_bind_time(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (lunch time)')
            cur.execute('insert into testpyodbc values (?)', ('11:30:29',))
            self.assertTrue(cur.rowcount in (-1, 1))
        finally:
            cur.close()
            con.close()

    def test_bind_timestamp(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (lunch timestamp)')
            cur.execute('insert into testpyodbc values (?)', ('2011-5-3 11:30:29',))
            self.assertTrue(cur.rowcount in (-1, 1))
        finally:
            cur.close()
            con.close()

    def test_lob_file(self):
        # pyodbc: bind BLOB as bytes (or pyodbc.Binary); no con.lob() API.
        logo = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'cubrid_logo.png')
        if os.path.isfile(logo):
            with open(logo, 'rb') as fh:
                raw = fh.read()
        else:
            raw = _MIN_PNG

        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (picture blob)')
            param = pyodbc.Binary(raw) if hasattr(pyodbc, 'Binary') else raw
            cur.execute('insert into testpyodbc values (?)', (param,))
            self.assertTrue(cur.rowcount in (-1, 1))
            con.commit()
            cur.execute('select picture from testpyodbc')
            row = cur.fetchone()
            self.assertIsNotNone(row)
            self.assertEqual(_as_bytes(row[0]), raw)
        finally:
            cur.close()
            con.close()

    def test_lob_string(self):
        # pyodbc: bind CLOB as str (Unicode).
        text = 'hello world'
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (content clob)')
            cur.execute('insert into testpyodbc values (?)', (text,))
            self.assertTrue(cur.rowcount in (-1, 1))
            con.commit()
            # Direct CLOB select can confuse pyodbc UTF-16 decoding on CUBRID ODBC.
            cur.execute('select cast(content as varchar(32768)) from testpyodbc')
            row = cur.fetchone()
            self.assertIsNotNone(row)
            got = row[0]
            if isinstance(got, bytes):
                got = got.decode('utf-8')
            self.assertEqual(str(got).rstrip(), text)
        finally:
            cur.close()
            con.close()

    def test_result_info(self):
        con = self._connect()
        cur = con.cursor()
        try:
            cur.execute('create table testpyodbc (id int primary key, name varchar(20))')
            con.commit()
            cur.execute('select * from testpyodbc')
            self.assertIsNotNone(cur.description)
            self.assertEqual(len(cur.description), 2)
            col_names = [cur.description[i][0].lower() for i in range(2)]
            self.assertEqual(col_names[0], 'id')
            self.assertEqual(col_names[1], 'name')
            pk_cols = _primary_key_column_names(cur, 'testpyodbc')
            self.assertEqual(pk_cols, ['id'])

            cur.execute('select id from testpyodbc')
            self.assertEqual(len(cur.description), 1)
            self.assertEqual(cur.description[0][0].lower(), 'id')
        finally:
            cur.close()
            con.close()


def suite():
    suite = unittest.TestSuite()
    suite.addTest(DatabaseTest('test_bind_timestamp'))
    return suite


if __name__ == '__main__':
    log_file = 'testpyodbc.result'
    with open(log_file, 'w', encoding='utf-8') as f:
        unittest.TextTestRunner(
            verbosity=2, stream=f).run(
            unittest.TestLoader().loadTestsFromTestCase(DatabaseTest))
