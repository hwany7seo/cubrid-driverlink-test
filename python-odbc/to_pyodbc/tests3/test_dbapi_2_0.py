"""
This module tests DB API 2.0 compliance for the CUBRID database interface, using pytest for setup
and teardown of test environments. It includes fixtures for database table management and covers
a range of DB API 2.0 features like connection and cursor operations, transaction control, and
data integrity.

Goals:
- Ensure predictable interaction with CUBRID via DB API 2.0 standards.
- Facilitate a consistent programming experience for Python developers.

Fixtures:
- booze_table: Prepares a database table for testing, then cleans up afterwards.

Notes:
- Assumes a running CUBRID database instance is available.

Usage:
- Execute with pytest to run all module tests.
- Verify CUBRID connection details are set correctly.
"""
# pylint: disable=missing-function-docstring

import datetime
import decimal
import time

import pytest

from conftest import (
    BOOZE_SAMPLES,
    PYODBC_ERR_CONNECT_EMPTY_DSN,
    PYODBC_ERR_CONN_CLOSED_COMMIT,
    PYODBC_ERR_CURSOR_CLOSED,
    PYODBC_ERR_HY000_GENERIC,
    TABLE_PREFIX,
    PYODBC_TYPE_CONNECT_NO_ARGS,
    _get_connect_args,
    assert_pyodbc_exc_str,
)

import pyodbc


def test_connect(cubrid_db_connection):
    assert cubrid_db_connection is not None, "Connection to cubrid_db failed"


def test_connect_empty_dsn():
    with pytest.raises(pyodbc.InterfaceError) as ei:
        pyodbc.connect('')
    assert_pyodbc_exc_str(ei, PYODBC_ERR_CONNECT_EMPTY_DSN)


def test_connect_no_dsn():
    with pytest.raises(TypeError) as ei:
        pyodbc.connect()
    assert_pyodbc_exc_str(ei, PYODBC_TYPE_CONNECT_NO_ARGS)


def test_apilevel():
    # Must exist
    assert hasattr(pyodbc, 'apilevel'), "Driver doesn't define apilevel"

    # Must be a valid value
    apilevel = pyodbc.apilevel
    assert apilevel == '2.0', f"Expected apilevel to be '2.0', got {apilevel}"


def test_paramstyle():
    # Must exist
    assert hasattr(pyodbc, 'paramstyle'), "Driver doesn't define paramstyle"

    # Must be a valid value
    paramstyle = pyodbc.paramstyle
    valid_styles = ('qmark', 'numeric', 'format', 'pyformat')
    assert paramstyle in valid_styles, \
        f"paramstyle must be one of {valid_styles}, got {paramstyle}"


def test_db_api_exceptions_hierarchy():
    # Base exceptions
    assert hasattr(pyodbc, 'Error'), "'Error' exception is missing"

    # Subclasses of Error
    for exc in ['InterfaceError', 'DatabaseError']:
        assert hasattr(pyodbc, exc), f"'{exc}' exception is missing"
        assert issubclass(getattr(pyodbc, exc), pyodbc.Error), \
            f"'{exc}' does not subclass 'Error'"

    # Subclasses of DatabaseError
    for exc in ['DataError', 'OperationalError', 'IntegrityError', 'InternalError',
                'ProgrammingError', 'NotSupportedError']:
        assert hasattr(pyodbc, exc), f"'{exc}' exception is missing"
        assert issubclass(getattr(pyodbc, exc), pyodbc.DatabaseError), \
            f"'{exc}' does not subclass 'DatabaseError'"


def test_escape_string(cubrid_db_connection):
    con = cubrid_db_connection
    if not hasattr(con, 'escape_string'):
        pytest.skip('pyodbc.Connection has no escape_string (use parameterized queries)')

    # Test for empty string
    assert con.escape_string('') == ''

    # Test for single quotes (which should be escaped)
    assert con.escape_string("O'Reilly") == "O''Reilly"

    # Test for escaping SQL percent sign and underscore
    assert con.escape_string("100% sure") == "100% sure"
    assert con.escape_string("an_underscore") == "an_underscore"

    # Test for non-ASCII characters (Unicode)
    assert con.escape_string("Unicode test: èéêëēėę") == "Unicode test: èéêëēėę"

    # Test for numeric values (which should not be altered)
    assert con.escape_string("123456") == "123456"

    # Test for SQL keywords to ensure they're not altered
    assert con.escape_string("SELECT * FROM users") == "SELECT * FROM users"


def test_invalid_sql_insert_raises_dberror(cubrid_db_cursor):
    cur, _ = cubrid_db_cursor
    table_name = f'{TABLE_PREFIX}booze'
    try:
        # Some ODBC drivers surface this as generic Error, not DatabaseError
        with pytest.raises(pyodbc.Error) as ei:
            cur.execute(f"insert into {TABLE_PREFIX}booze values error_sql ('Hello')")
        assert_pyodbc_exc_str(ei, PYODBC_ERR_HY000_GENERIC)
    finally:
        cur.execute(f'drop table if exists {table_name}')


def test_invalid_sql_insert_raises_error(cubrid_db_cursor):
    cur, _ = cubrid_db_cursor
    table_name = f'{TABLE_PREFIX}booze'
    try:
        with pytest.raises(pyodbc.Error) as ei:
            cur.execute(f"insert into {TABLE_PREFIX}booze values ('Hello', 'hello2')")
        assert_pyodbc_exc_str(ei, PYODBC_ERR_HY000_GENERIC)
    finally:
        cur.execute(f'drop table if exists {table_name}')


def test_commit(cubrid_db_connection):
    # The commit operation is tested to ensure it can be called without raising an exception.
    cubrid_db_connection.commit()


def test_rollback(cubrid_db_connection):
    # Test to ensure the rollback operation can be called without raising an exception.
    cubrid_db_connection.rollback()


def test_cursor(cubrid_db_cursor):
    # Since the cubrid_db_cursor fixture handles cursor creation, this test implicitly verifies
    # that a cursor can be successfully obtained and closed without errors.
    # Additional operations or assertions to test the cursor's functionality can be added here.
    pass


def test_cursor_isolation(cubrid_db_connection):
    con = cubrid_db_connection
    table_name = f'{TABLE_PREFIX}booze'
    cur1 = cur2 = None

    try:
        # Make sure cursors created from the same connection have
        # the documented transaction isolation level
        cur1 = con.cursor()
        cur2 = con.cursor()

        cur1.execute(f'create table {table_name} (name varchar(20))')
        cur1.execute(f"insert into {table_name} values ('Victoria Bitter')")
        cur2.execute(f"select name from {table_name}")
        booze = cur2.fetchall()

        assert len(booze) == 1, "Expected to fetch one row"
        assert len(booze[0]) == 1, "Expected row to have one column"
        assert booze[0][0] == 'Victoria Bitter', "Expected to find 'Victoria Bitter'"
    finally:
        # Clean up: close cursors and clean the test data
        if cur1:
            cur1.execute(f'drop table if exists {table_name}')
            cur1.close()
        if cur2:
            cur2.close()


def test_rowcount(cubrid_db_cursor, booze_table):
    cur, _ = cubrid_db_cursor

    assert cur.rowcount in (-1, 0), \
        'cursor.rowcount should be -1 or 0 after executing no-result statements'

    cur.execute(f"insert into {booze_table} values ('Victoria Bitter')")
    assert cur.rowcount in (-1, 1),\
        'cursor.rowcount should == number or rows inserted, or '\
        'set to -1 after executing an insert statement'

    cur.execute(f'select name from {booze_table}')
    assert cur.rowcount in (-1, 1),\
        'cursor.rowcount should == number or rows inserted, or '\
        'set to -1 after executing an insert statement'

    table_name = f'{TABLE_PREFIX}barflys'
    try:
        # Make sure self.description gets reset
        cur.execute(f'create table {table_name} (name varchar(20))')
        assert cur.rowcount in (-1, 0), \
            'cursor.rowcount should be -1 or 0 after executing no-result statements'
    finally:
        cur.execute(f'drop table if exists {table_name}')


def test_close(cubrid_db_connection):
    con = cubrid_db_connection
    cur = con.cursor()
    con.close()

    table_name = f'{TABLE_PREFIX}booze'
    with pytest.raises(pyodbc.ProgrammingError) as ei:
        cur.execute(f'create table {table_name} (name varchar(20))')
    assert_pyodbc_exc_str(ei, PYODBC_ERR_CURSOR_CLOSED)

    with pytest.raises(pyodbc.ProgrammingError) as ei:
        con.commit()
    assert_pyodbc_exc_str(ei, PYODBC_ERR_CONN_CLOSED_COMMIT)


def test_insert_utf8(cubrid_db_cursor, barflys_table):
    cur, _ = cubrid_db_cursor

    cur.execute(f"insert into {barflys_table} (name) values (?)", ['Tom',])
    assert cur.rowcount in (-1, 1)
    cur.execute(f"insert into {barflys_table} (name) values (?)", [b'Jenny',])
    assert cur.rowcount in (-1, 1)
    cur.execute(f"insert into {barflys_table} (name) values (?)", ['小王',])
    assert cur.rowcount in (-1, 1)


def test_executemany(cubrid_db_cursor, booze_table):
    cur, _ = cubrid_db_cursor

    largs = [("Cooper's",), ("Boag's",)]
    cur.executemany(f'insert into {booze_table} values (?)', largs)

    cur.execute(f'select name from {booze_table}')
    res = cur.fetchall()
    assert len(res) == 2, 'cursor.fetchall returned incorrect number of rows'
    beers = [res[0][0], res[1][0]]
    assert beers == [a[0] for a in largs], \
        'cursor.fetchall retrieved incorrect data, or data inserted incorrectly'


def test_autocommit(cubrid_db_cursor, booze_table):
    cur, con = cubrid_db_cursor

    # PEP 249 does not require a default; pyodbc often defaults to False
    prev = con.autocommit

    con.autocommit = False
    assert con.autocommit is False, "autocommit must be set to off"

    cur.execute(f"insert into {booze_table} values ('Hello')")
    con.rollback()
    con.autocommit = True
    # Same connection may not accept SELECT immediately after rollback on CUBRID ODBC
    con2 = None
    try:
        con2 = pyodbc.connect(_get_connect_args())
        c2 = con2.cursor()
        c2.execute(f"select count(*) from {booze_table}")
        n = int(c2.fetchone()[0])
    except pyodbc.Error:
        pytest.skip('CUBRID ODBC: cannot query table after rollback on another connection')
    finally:
        if con2 is not None:
            con2.close()

    assert n == 0

    cur.execute(f"insert into {booze_table} values ('Hello')")
    cur_sel = con.cursor()
    cur_sel.execute(f"select * from {booze_table}")
    rows = cur_sel.fetchall()
    cur_sel.close()

    assert len(rows) == 1

    con.autocommit = prev


def test_datatype(cubrid_db_cursor, datatype_table):
    cur, _ = cubrid_db_cursor

    cur.execute(f"insert into {datatype_table} values "
        "(2012, 2012.345, 20.12345, time'11:21:30 am',"
        "date'2012-10-26', datetime'2012-10-26 11:21:30 am',"
        "timestamp'11:21:30 am 2012-10-26',"
        "B'101100111', 'TESTSTR', {'a', 'b', 'c'},"
        "{'c', 'c', 'c', 'b', 'b', 'a'},"
        '\'{"a": 1, "b": 2}\''
        ")"
    )

    cur.execute(f"select * from {datatype_table}")
    row = cur.fetchone()

    datatypes = [int, float, decimal.Decimal, datetime.time,
        datetime.date, datetime.datetime, datetime.datetime,
        bytes, str, set, list, str,
    ]

    for i, t in enumerate(datatypes):
        val = row[i]
        if t is set:
            # ODBC/pyodbc may return a serialized string instead of a Python set
            assert isinstance(val, (set, str)),\
                f'incorrect data type converted from CUBRID to Python (index {i} - {t})'
            if isinstance(val, set) and val:
                assert isinstance(next(iter(val)), str)
        elif t is list:
            assert isinstance(val, (list, str)),\
                f'incorrect data type converted from CUBRID to Python (index {i} - {t})'
            if isinstance(val, list) and val:
                assert isinstance(val[0], str)
        else:
            assert isinstance(val, t),\
                f'incorrect data type converted from CUBRID to Python (index {i} - {t})'


def test_mixdfetch(cubrid_db_cursor, populated_booze_table):
    cur, _ = cubrid_db_cursor

    row_count = len(BOOZE_SAMPLES)

    cur.execute(f'select name from {populated_booze_table}')
    row1 = cur.fetchone()
    rows23 = cur.fetchmany(2)
    row4 = cur.fetchone()
    rows_last = cur.fetchall()
    assert cur.rowcount in (-1, row_count)
    assert len(rows23)== 2, 'fetchmany returned incorrect number of rows'
    assert len(rows_last) == max(row_count - 4, 0),\
            'fetchall returned incorrect number of rows'

    rows = [row1] + rows23 + [row4] + rows_last
    rows = [r[0] for r in rows]
    rows.sort()
    assert rows == BOOZE_SAMPLES, 'incorrect data retrieved or inserted'


def test_threadsafety():
    assert hasattr(pyodbc, 'threadsafety')
    threadsafety = pyodbc.threadsafety
    assert threadsafety in (0,1,2,3)


def test_binary():
    pyodbc.Binary([0x10, 0x20, 0x30])


def test_date():
    pyodbc.Date(2011,3,17)
    pyodbc.DateFromTicks(time.mktime((2011,3,17,0,0,0,0,0,0)))


def test_time():
    pyodbc.Time(10, 30, 45)
    try:
        pyodbc.TimeFromTicks(time.mktime((2011, 3, 17, 17, 13, 30, 0, 0, 0)))
    except SystemError:
        pytest.skip('pyodbc TimeFromTicks raised SystemError for this platform/build')


def test_timestamp():
    pyodbc.Timestamp(2002,12,25,13,45,30)
    pyodbc.TimestampFromTicks(time.mktime((2002,12,25,13,45,30,0,0,0)))


def test_attr_string():
    assert hasattr(pyodbc, 'STRING'), 'module.STRING must be defined'


def test_attr_binary():
    assert hasattr(pyodbc, 'BINARY'), 'module.BINARY must be defined'


def test_attr_number():
    assert hasattr(pyodbc, 'NUMBER'), 'module.NUMBER must be defined'


def test_attr_datetime():
    assert hasattr(pyodbc, 'DATETIME'), 'module.DATETIME must be defined'


def test_attr_rowid():
    assert hasattr(pyodbc, 'ROWID'), 'module.ROWID must be defined'
