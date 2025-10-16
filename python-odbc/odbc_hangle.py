import pyodbc
import time

class ODBC:

    insert_count = 5
    dsn = "CUBRID Driver"
    uid = "dba"
    pwd = ""

    def __init__(self):
        self.report_sampling = {}
        self.table_name = "test_table"
        self.report = {}
        self.test_insert_count = self.insert_count
        print("Connecting to database...", flush=True)
        self.conn = pyodbc.connect(
            DSN=self.dsn, 
            UID=self.uid, 
            PWD=self.pwd,
            charset="utf-8"
        )
        self.conn.autocommit = False
        print("Database connected successfully", flush=True)
        self.cur = self.conn.cursor()
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
            if i % 10 == 0:
                cursor.executemany(sql, [(i, f"한글테스트{i}")])
            else:
                cursor.executemany(sql, [(i, f"pyodbc{i}")])
        self.conn.commit()
        end = time.time()
        print("Data inserted. (elapsed time: {:.2f}s)".format(end - start))

    def select_count(self):
        sql = f'select count(*) from {self.table_name}'
        self.cur.execute(sql)
        print("Data count after insert: {}".format(self.cur.fetchone()[0]))
        
    def select_all(self):
        sql = f'select * from {self.table_name}'
        self.cur.execute(sql)
        print("Data all selected. rowCount: {}".format(self.cur.rowcount))
        print("Sample data (including Korean):")
        for i, row in enumerate(self.cur):
            if i < 5:
                print(f"  Row {i+1}: {row}")
            else:
                break

    def select(self):
        sql = f'select * from {self.table_name} where id = ?'
        start = time.time()
        row_count = 0
        for i in range(self.test_insert_count):
            self.cur.execute(sql, i)
            row = self.cur.fetchone()
            print(f"row: {row}")
            print(f"row[0]: {row[0]}")
            print(f"row[1]: {row[1]}")
            row_count += 1
        end = time.time()
        print("data selected. rowCount: {} (elapsed time: {:.2f}s)".format(
            row_count, end - start))

    def select_korean(self):
        print("Testing Korean data search...", flush=True)
        sql = f"select * from {self.table_name} where name like '%한글%'"
        self.cur.execute(sql)
        korean_rows = self.cur.fetchall()
        print(f"Korean data found: {len(korean_rows)} rows")
        for row in korean_rows:
            print(f"  Korean row: {row}")

    def test(self):
        self.insert()
        self.select_count()
        self.select_all()
        self.select()
        self.select_korean()

if __name__ == "__main__":
    odbc = ODBC()


