import unittest
import pyodbc
import locale
import time
import datetime
from decimal import Decimal
from xml.dom import minidom

class FetchoneDescriptionTest(unittest.TestCase):
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

        def test_desc_num(self):
#               test description of int type
                sqlSelect = "select * from numeric_db"
                self.cur.execute(sqlSelect)
                dataDesc = self.cur.description                
                dataCheck = (('c_int', int, None, 11, 11, 0, True), ('c_short', int, None, 6, 6, 0, True), ('c_numeric', Decimal, None, 15, 15, 0, True), ('c_float', float, None, 15, 15, 0, True), ('c_double', float, None, 22, 22, 0, True), ('c_monetary', float, None, 22, 22, 0, True))
                self.assertEqual(dataCheck, dataDesc)

        def test_desc_datetime(self):
#               test description of datetime type
                sqlSelect = "select * from datetime_db"
                self.cur.execute(sqlSelect)
                dataDesc = self.cur.description
                dataCheck = (('c_date', datetime.date, None, 10, 10, 0, True), ('c_time', datetime.time, None, 11, 11, 0, True), ('c_datetime', datetime.datetime, None, 23, 23, 0, True), ('c_timestamp', datetime.datetime, None, 23, 23, 0, True))
                self.assertEqual(dataCheck, dataDesc)

        def test_desc_bit(self):
#               test description of bit type
                sqlSelect = "select * from bit_db"
                self.cur.execute(sqlSelect)
                dataDesc = self.cur.description
                dataCheck = (('c_bit', bytearray, None, 4, 4, 0, True), ('c_varbit', bytearray, None, 4, 4, 0, True))
                print("dataDesc: ", dataDesc)
                print("dataCheck: ", dataCheck)
                self.assertEqual(dataCheck, dataDesc)

        def test_desc_char(self):
#               test description of char type
                sqlSelect = "select * from character_db"
                self.cur.execute(sqlSelect)
                dataDesc = self.cur.description
                dataCheck = (('c_char', str, None, 4, 4, 0, True), ('c_varchar', str, None, 4, 4, 0, True), ('c_string', str, None, 1073741823, 1073741823, 0, True), ('c_nchar', str, None, 4, 4, 0, True), ('c_varnchar', str, None, 4, 4, 0, True))
                self.assertEqual(dataCheck, dataDesc)

        def test_desc_collection(self):
#               test description of collection type
                sqlSelect = "select * from collection_db"
                self.cur.execute(sqlSelect)
                dataDesc = self.cur.description
                dataCheck = (('c_set', str, None, 1073741823, 1073741823, 0, True), ('c_multiset', str, None, 1073741823, 1073741823, 0, True), ('c_sequence', str, None, 1073741823, 1073741823, 0, True))
                self.assertEqual(dataCheck, dataDesc)

        def test_all(self):
                sqlSelect = "select * from numeric_db"
                self.cur.execute(sqlSelect)
                print(self.cur.description)
                sqlSelect = "select * from datetime_db"
                self.cur.execute(sqlSelect)
                print(self.cur.description)
                sqlSelect = "select * from bit_db"
                self.cur.execute(sqlSelect)
                print(self.cur.description)
                sqlSelect = "select * from character_db"
                self.cur.execute(sqlSelect)
                print(self.cur.description)
                sqlSelect = "select * from collection_db"
                self.cur.execute(sqlSelect)
                print(self.cur.description)


if __name__ == '__main__':
        suite = unittest.TestLoader().loadTestsFromTestCase(FetchoneDescriptionTest)
        unittest.TextTestRunner(verbosity=2).run(suite)
