import pyodbc
import time

class ODBC:

    insert_count = 100
    driver = "CUBRID Driver Unicode"
    server = "192.168.2.32"
    port = 33000
    uid = "dba"
    pwd = ""
    db_name = "demodb"

    def __init__(self):
        self.report_sampling = {}
        self.table_name = "test_table"
        self.report = {}
        self.test_insert_count = self.insert_count
        print("Connecting to database...", flush=True)
        self.conn = pyodbc.connect(f"driver={self.driver};server={self.server};port={self.port};uid={self.uid};pwd={self.pwd};db_name={self.db_name};")
        self.conn.autocommit = False
        print("Database connected successfully", flush=True)
        self.cur = self.conn.cursor()
        self.cur.execute("SET NAMES utf8;")
        self.initialize()
        self.test()   

    def initialize(self):
        print("Initializing test table...", flush=True)
        self.cur.execute("drop table if exists " + self.table_name)
        sql = f"""CREATE TABLE {self.table_name} (id INT, name VARCHAR(255));"""
        self.cur.execute(sql)
        print("Test table created successfully", flush=True)
    
    def insert(self):
        print("Inserting data...", flush=True)
        start = time.time()
        sql = f'insert into {self.table_name} values (?, ?)'
        cursor = self.conn.cursor()
        for i in range(self.test_insert_count):
            cursor.executemany(sql, [(i, f"pyodbc{i}")])
        self.conn.commit()
        end = time.time()
        print("Data inserted. (elapsed time: {:.2f}s)".format(end - start))

    def select_count(self):
        sql = f'select count(*) from {self.table_name}'
        self.cur.execute(sql)
        print("Data count after insert: {}".format(self.cur.fetchone()[0]))

    def select(self):
        sql = f'select * from {self.table_name} where id = ?'
        start = time.time()
        row_count = 0
        for i in range(self.test_insert_count):
            self.cur.execute(sql, i)
            row = self.cur.fetchone()
            row_count += 1
        end = time.time()
        print("data selected. rowCount: {} (elapsed time: {:.2f}s)".format(
            row_count, end - start))

    def test(self):
        self.insert()
        self.select_count()
        self.select()

if __name__ == "__main__":
    odbc = ODBC()


