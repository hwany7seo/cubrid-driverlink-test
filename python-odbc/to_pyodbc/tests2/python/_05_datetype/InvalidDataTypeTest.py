import unittest
import pyodbc
import locale
import time
import datetime 
from datetime import time
from datetime import date
from datetime import datetime
from random import Random
from xml.dom import minidom

class InvalidDataTypeTest(unittest.TestCase):
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

                sqlDrop = "drop table if exists numeric_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists datetime_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists bit_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists character_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists collection_db"
                self.cur.execute(sqlDrop)

                sqlCreate = "create table numeric_db(c_int int, c_short short,c_numeric numeric,c_float float,c_double double,c_monetary monetary)"
                self.cur.execute(sqlCreate)
                sqlCreate = "create table datetime_db(c_date date, c_time time, c_datetime datetime, c_timestamp timestamp)"
                self.cur.execute(sqlCreate)
                sqlCreate = "create table bit_db(c_bit bit(8),c_varbit bit varying(8))"
                self.cur.execute(sqlCreate)
                sqlCreate = "create table character_db(c_char char(4),c_varchar varchar(4),c_string string,c_nchar nchar(4),c_varnchar nchar varying(4))"
                self.cur.execute(sqlCreate)
                sqlCreate = "create table collection_db(c_set set,c_multiset multiset, c_sequence sequence)"
                self.cur.execute(sqlCreate)

        def tearDown(self):
                sqlDrop = "drop table if exists numeric_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists datetime_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists bit_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists character_db"
                self.cur.execute(sqlDrop)
                sqlDrop = "drop table if exists collection_db"
                self.cur.execute(sqlDrop)
                self.cur.close()
                self.con.close()

        def test_int(self):
#               test invalid int type
                dataList = [2147483648,-2147483649]
                sqlInsert = "insert into numeric_db(c_int) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + '%d'%i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])

        def test_short(self):
#               test invalid short type
                dataList = [32768,-32769]
                sqlInsert = "insert into numeric_db(c_short) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + '%d'%i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])
        
        def _test_char(self):
#                test invalid string type
                dataList = ['a']
                sqlInsert = "insert into character_db(c_char) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])


        def test_nchar(self):
#               test invalid string type
                dataList = ['a']
                sqlInsert = "insert into character_db(c_nchar) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])

        def _test_varchar(self):
#                print("test invalid string type")
                dataList = ['abcdefg']
                sqlInsert = "insert into character_db(c_varchar) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])

        def test_varnchar(self):
#                print("test invalid string type")
                dataList = [self.generateStr(10737)]
                sqlInsert = "insert into character_db(c_varnchar) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])

        def _test_string(self):
#                print("test invalid string type")
                dataList = [self.generateStr(10737)]
                sqlInsert = "insert into character_db(c_string) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                try:
                        self.cur.execute(sqlInsert)
                        self.fail("Have insert invalid data!")
                except pyodbc.Error as e:
                        self.assertEqual("HY000", e.args[0])

        def test_date(self):
#               test invalid date type
                dataList = [date.min,date.today(),date.max]
                sqlInsert = "insert into datetime_db(c_date) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i.isoformat() + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select * from datetime_db"
                self.cur.execute(sqlSelect)
                for i in range(rowNum):
                        data = self.cur.fetchone()
                        self.assertEqual(dataList[i], data[0])

        def test_time(self):
#               test invalid time type
                dataList = [time.min,time.max]
                sqlInsert = "insert into datetime_db(c_time) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i.isoformat() + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select * from datetime_db"
                self.cur.execute(sqlSelect)
                for i in range(rowNum):
                        data = self.cur.fetchone()
                        self.assertEqual(dataList[i].isoformat().rstrip('9').rstrip('.'), data[1].isoformat())

        def test_datetime(self):
#               test invalid datetime type
                dataList = [datetime.min,datetime.today(),datetime.now(),datetime.max]
                sqlInsert = "insert into datetime_db(c_datetime) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i.isoformat() + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select * from datetime_db"
                self.cur.execute(sqlSelect)
                #for i in range(rowNum):
                data = self.cur.fetchone()
                self.assertEqual('0001-01-01 00:00:00', data[2].isoformat(" "))

        def test_timestamp(self):
#               test invalid datetime type
                checkData = str(datetime.now().year) + '-10-31 00:00:00'
                dataList = ['10/31','10/31/2008','13:15:45 10/31/2008']
                dataCheck = [checkData,'2008-10-31 00:00:00','2008-10-31 13:15:45']
                sqlInsert = "insert into datetime_db(c_timestamp) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "('" + i + "'),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select c_timestamp from datetime_db"
                self.cur.execute(sqlSelect)
                for i in range(rowNum):
                        data = self.cur.fetchone()
                        self.assertEqual(dataCheck[i], data[0].isoformat(" "))

        def _test_bit(self):
#               test invalid bit type
                dataList = ['B\'1\'','B\'1010\'']
                dataCheck = ['80','A0']
                sqlInsert = "insert into bit_db(c_bit) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select * from bit_db"
                self.cur.execute(sqlSelect)
                for i in range(rowNum):
                        data = self.cur.fetchone()
                        self.assertEqual(dataCheck[i], data[0])
        """
        def test_varbit(self):
#               test invalid bit varying type
                dataList = ['B\'1\'','B\'1010\'']
                dataCheck = ['8','A0']
                sqlInsert = "insert into bit_db(c_varbit) values "
                for i in dataList:
                        sqlInsert = sqlInsert + "(" + i + "),"
                sqlInsert = sqlInsert.rstrip(',')
                self.cur.execute(sqlInsert)
                rowNum = self.cur.rowcount
                sqlSelect = "select c_varbit from bit_db"
                self.cur.execute(sqlSelect)
                for i in range(rowNum):
                        data = self.cur.fetchone()
                        self.assertEqual(dataCheck[i], data[0])
        """

        def randomStr(self,length=8):
                str=''
                chars='AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789'
                lenChars = len(chars)-1
                rand = Random()
                for i in range(length):
                        str+=chars[random.randint(0,lenChars)]
                return str

        def generateStr(self,length=8):
                str=''
                c='a'
                for i in range(length):
                        str+=c
                return str

if __name__ == '__main__':
        suite = unittest.TestLoader().loadTestsFromTestCase(InvalidDataTypeTest)
        unittest.TextTestRunner(verbosity=2).run(suite)
