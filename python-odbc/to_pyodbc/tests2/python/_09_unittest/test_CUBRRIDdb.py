"""DB-API 2.0 compliance tests for pyodbc with CUBRID ODBC driver.

Unsupported features: use pass with docstring for description to show in output.
Example:
    def test_unsupported_feature(self):
        \"\"\"pyodbc with cubrid-odbc is not support\"\"\"
        pass
"""
import unittest
import pyodbc
import time
from xml.dom import minidom


class DBAPI20Test(unittest.TestCase):
    driver = pyodbc
    xmlt = minidom.parse('configuration/python_config.xml')
    ips = xmlt.childNodes[0].getElementsByTagName('ip')
    ip = ips[0].childNodes[0].toxml()
    ports = xmlt.childNodes[0].getElementsByTagName('port')
    port = ports[0].childNodes[0].toxml()
    dbnames = xmlt.childNodes[0].getElementsByTagName('dbname')
    dbname = dbnames[0].childNodes[0].toxml()
    conStr = "DRIVER={CUBRID ODBC Driver};SERVER="+ip+";PORT="+port+";UID=dba;PWD=;DB_NAME="+dbname
    connect_args = (conStr,)
    connect_kw_args = {}
    table_prefix = 'dbapi20test_'

    ddl1 = 'create table %sbooze (name varchar(20))' % table_prefix
    ddl2 = 'create table %sbarflys (name varchar(20))' % table_prefix
    xddl1 = 'drop table if exists %sbooze' % table_prefix
    xddl2 = 'drop table if exists %sbarflys' % table_prefix

    def executeDDL1(self, cursor):
        cursor.execute(self.ddl1)

    def executeDDL2(self, cursor):
        cursor.execute(self.ddl2)

    def setup(self):
        print("setup... ")

    def tearDown(self):
        try:
            con = self._connect()
            cur = con.cursor()
            cur.execute(self.xddl1)
            cur.execute(self.xddl2)
            cur.close()
            con.close()
        except Exception:
            pass

    def _connect(self):
        try:
            return self.driver.connect(*self.connect_args, **self.connect_kw_args)
        except AttributeError:
            self.fail("No connect method found in self.driver module")

    def test_connect(self):
        con = self._connect()
        con.close()

    def test_apilevel(self):
        try:
            apilevel = self.driver.apilevel
            self.assertEqual(apilevel, '2.0')
        except AttributeError:
            self.fail("Driver doesn't define apilevel")

    def test_paramstyle(self):
        try:
            paramstyle = self.driver.paramstyle
            self.assertTrue(paramstyle in ('qmark', 'numeric', 'format', 'pyformat'))
        except AttributeError:
            self.fail("Driver doesn't define paramstyle")

    def test_Exceptions(self):
        self.assertTrue(issubclass(self.driver.Error, Exception))
        self.assertTrue(issubclass(self.driver.InterfaceError, self.driver.Error))
        self.assertTrue(issubclass(self.driver.DatabaseError, self.driver.Error))

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
        finally:
            cur.close()
            con.close()

    def test_cursor_isolation(self):
        con = self._connect()
        try:
            cur1 = con.cursor()
            cur2 = con.cursor()
            self.executeDDL1(cur1)
            cur1.execute("insert into %sbooze values ('Victoria Bitter')" % self.table_prefix)
            cur2.execute("select name from %sbooze" % self.table_prefix)
            booze = cur2.fetchall()
            self.assertEqual(len(booze), 1)
            self.assertEqual(len(booze[0]), 1)
            self.assertEqual(booze[0][0], 'Victoria Bitter')
        finally:
            con.close()

    def test_description(self):
        """pyodbc returns (name, type_class, ...); type may differ from DB-API integer codes."""
        con = self._connect()
        try:
            cur = con.cursor()
            self.executeDDL1(cur)
            self.assertEqual(cur.description, None,
                            'cursor.description should be none after DDL')
            cur.execute('select name from %sbooze' % self.table_prefix)
            self.assertEqual(len(cur.description), 1,
                            'cursor.description describes too many columns')
            self.assertGreaterEqual(len(cur.description[0]), 2,
                                    'cursor.description[x] must have at least 2 elements')
            self.assertEqual(cur.description[0][0].lower(), 'name',
                            'cursor.description[x][0] must return column name')
            self.executeDDL2(cur)
            self.assertEqual(cur.description, None,
                            'cursor.description not being set to None after DDL')
        finally:
            con.close()

    def test_rowcount(self):
        """pyodbc/cubrid-odbc may return 0 or -1 for rowcount after DDL."""
        con = self._connect()
        try:
            cur = con.cursor()
            self.executeDDL1(cur)
            self.assertTrue(cur.rowcount in (-1, 0),
                            'cursor.rowcount should be -1 or 0 after DDL')
            cur.execute("insert into %sbooze values ('Victoria Bitter')" % self.table_prefix)
            self.assertTrue(cur.rowcount in (-1, 1),
                            'cursor.rowcount should == 1 or -1 after insert')
            cur.execute("select name from %sbooze" % self.table_prefix)
            self.assertTrue(cur.rowcount in (-1, 1),
                            'cursor.rowcount should == 1 or -1 after select')
            self.executeDDL2(cur)
            self.assertTrue(cur.rowcount in (-1, 0),
                            'cursor.rowcount not being reset after DDL')
        finally:
            con.close()

    def test_close(self):
        con = self._connect()
        try:
            cur = con.cursor()
        finally:
            con.close()

    def test_execute(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self._paraminsert(cur)
        finally:
            con.close()

    def _paraminsert(self, cur):
        self.executeDDL1(cur)
        cur.execute("insert into %sbooze values ('Victoria Bitter')" % self.table_prefix)
        self.assertTrue(cur.rowcount in (-1, 1))

        cur.execute('insert into %sbooze values (?)' % self.table_prefix, ("Cooper's",))
        self.assertTrue(cur.rowcount in (-1, 1))

        cur.execute('select name from %sbooze' % self.table_prefix)
        res = cur.fetchall()
        self.assertEqual(len(res), 2, 'cursor.fetchall returned too few rows')
        beers = [res[0][0], res[1][0]]
        beers.sort()
        self.assertEqual(beers[0], "Cooper's", 'incorrect data retrieved')
        self.assertEqual(beers[1], "Victoria Bitter", 'incorrect data retrieved')

    def test_executemany(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self.executeDDL1(cur)
            largs = [("Cooper's",), ("Boag's",)]
            cur.executemany('insert into %sbooze values (?)' % self.table_prefix, largs)
            cur.execute('select name from %sbooze' % self.table_prefix)
            res = cur.fetchall()
            self.assertEqual(len(res), 2, 'cursor.fetchall retrieved incorrect number of rows')
            beers = [res[0][0], res[1][0]]
            beers.sort()
            self.assertEqual(beers[0], "Boag's", 'incorrect data retrieved')
            self.assertEqual(beers[1], "Cooper's", 'incorrect data retrieved')
        finally:
            con.close()

    def test_fetchone(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self.executeDDL1(cur)
            cur.execute('select name from %sbooze' % self.table_prefix)
            self.assertEqual(cur.fetchone(), None,
                            'cursor.fetchone should return None if no rows')
            self.assertTrue(cur.rowcount in (-1, 0))
            cur.execute("insert into %sbooze values ('Victoria Bitter')" % self.table_prefix)
            cur.execute("select name from %sbooze" % self.table_prefix)
            r = cur.fetchone()
            self.assertEqual(len(r), 1, 'cursor.fetchone should have retrieved a single row')
            self.assertEqual(r[0], 'Victoria Bitter', 'cursor.fetchone retrieved incorrect data')
            self.assertEqual(cur.fetchone(), None,
                            'cursor.fetchone should return None if no more rows')
            self.assertTrue(cur.rowcount in (-1, 1))
        finally:
            con.close()

    samples = [
        'Carlton Cold',
        'Carlton Draft',
        'Mountain Goat',
        'Redback',
        'Victoria Bitter',
        'XXXX'
    ]

    def _populate(self, cur):
        """pyodbc executemany requires sequence of sequences: [(s,), (s,), ...]"""
        self.executeDDL1(cur)
        samples_tuples = [(s,) for s in self.samples]
        cur.executemany('insert into %sbooze values (?)' % self.table_prefix, samples_tuples)

    def test_fetchmany(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self._populate(cur)

            cur.execute('select name from %sbooze' % self.table_prefix)
            r = cur.fetchmany()
            self.assertEqual(len(r), 1,
                            'cursor.fetchmany default should get 1 row')
            cur.arraysize = 10
            r = cur.fetchmany(3)
            self.assertEqual(len(r), 3, 'cursor.fetchmany(3) should get 3 rows')
            r = cur.fetchmany(4)
            self.assertEqual(len(r), 2, 'cursor.fetchmany(4) should get 2 rows')
            r = cur.fetchmany(4)
            self.assertEqual(len(r), 0, 'cursor.fetchmany should return empty after exhausted')
            self.assertTrue(cur.rowcount in (-1, 6))

            cur.arraysize = 4
            cur.execute('select name from %sbooze' % self.table_prefix)
            r = cur.fetchmany()
            self.assertEqual(len(r), 4, 'cursor.arraysize not honoured')
            r = cur.fetchmany()
            self.assertEqual(len(r), 2)
            r = cur.fetchmany()
            self.assertEqual(len(r), 0)
            self.assertTrue(cur.rowcount in (-1, 6))

            cur.arraysize = 6
            cur.execute('select name from %sbooze' % self.table_prefix)
            rows = cur.fetchmany()
            self.assertTrue(cur.rowcount in (-1, 6))
            self.assertEqual(len(rows), 6)
            rows = [r[0] for r in rows]
            rows.sort()
            for i in range(6):
                self.assertEqual(rows[i], self.samples[i], 'incorrect data by fetchmany')
            rows = cur.fetchmany()
            self.assertEqual(len(rows), 0)
            self.assertTrue(cur.rowcount in (-1, 6))

            self.executeDDL2(cur)
            cur.execute('select name from %sbarflys' % self.table_prefix)
            r = cur.fetchmany()
            self.assertEqual(len(r), 0)
            self.assertTrue(cur.rowcount in (-1, 0))
        finally:
            con.close()

    def test_fetchall(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self._populate(cur)

            cur.execute('select name from %sbooze' % self.table_prefix)
            rows = cur.fetchall()
            self.assertTrue(cur.rowcount in (-1, len(self.samples)))
            self.assertEqual(len(rows), len(self.samples),
                            'cursor.fetchall did not retrieve all rows')
            rows = [r[0] for r in rows]
            rows.sort()
            for i in range(len(self.samples)):
                self.assertEqual(rows[i], self.samples[i],
                                'cursor.fetchall retrieved incorrect rows')
            rows = cur.fetchall()
            self.assertEqual(len(rows), 0,
                            'cursor.fetchall should return empty after fetched')
            self.assertTrue(cur.rowcount in (-1, len(self.samples)))

            self.executeDDL2(cur)
            cur.execute('select name from %sbarflys' % self.table_prefix)
            rows = cur.fetchall()
            self.assertTrue(cur.rowcount in (-1, 0))
            self.assertEqual(len(rows), 0)
        finally:
            con.close()

    def test_mixdfetch(self):
        con = self._connect()
        try:
            cur = con.cursor()
            self._populate(cur)

            cur.execute('select name from %sbooze' % self.table_prefix)
            rows1 = cur.fetchone()
            rows23 = cur.fetchmany(2)
            rows4 = cur.fetchone()
            rows56 = cur.fetchall()
            self.assertTrue(cur.rowcount in (-1, 6))
            self.assertEqual(len(rows23), 2, 'fetchmany returned incorrect number')
            self.assertEqual(len(rows56), 2, 'fetchall returned incorrect number')

            rows = [rows1[0]]
            rows.extend([rows23[0][0], rows23[1][0]])
            rows.append(rows4[0])
            rows.extend([rows56[0][0], rows56[1][0]])
            rows.sort()
            for i in range(len(self.samples)):
                self.assertEqual(rows[i], self.samples[i], 'incorrect data retrieved')
        finally:
            con.close()

    def test_threadsafety(self):
        try:
            threadsafety = self.driver.threadsafety
            self.assertTrue(threadsafety in (0, 1, 2, 3))
        except AttributeError:
            self.fail("Driver doesn't define threadsafety")

    def test_Date(self):
        d1 = self.driver.Date(2011, 3, 17)
        d2 = self.driver.DateFromTicks(int(time.mktime((2011, 3, 17, 0, 0, 0, 0, 0, 0))))

    def test_Time(self):
        t1 = self.driver.Time(10, 30, 45)
        t2 = self.driver.TimeFromTicks(int(time.mktime((2011, 3, 17, 17, 13, 30, 0, 0, 0))))

    def test_Timestamp(self):
        t1 = self.driver.Timestamp(2002, 12, 25, 13, 45, 30)
        t2 = self.driver.TimestampFromTicks(
            int(time.mktime((2002, 12, 25, 13, 45, 30, 0, 0, 0))))

    def test_STRING(self):
        self.assertTrue(hasattr(self.driver, 'STRING'), 'module.STRING must be defined')

    def test_BINARY(self):
        self.assertTrue(hasattr(self.driver, 'BINARY'), 'module.BINARY must be defined')

    def test_NUMBER(self):
        self.assertTrue(hasattr(self.driver, 'NUMBER'), 'module.NUMBER must be defined')

    def test_DATETIME(self):
        self.assertTrue(hasattr(self.driver, 'DATETIME'), 'module.DATETIME must be defined')

    def test_ROWID(self):
        self.assertTrue(hasattr(self.driver, 'ROWID'), 'module.ROWID must be defined')


def suite():
    suite = unittest.TestSuite()
    suite.addTest(DBAPI20Test("test_connect"))
    return suite


if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(DBAPI20Test)
    unittest.TextTestRunner(verbosity=2).run(suite)
