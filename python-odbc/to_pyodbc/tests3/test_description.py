# pylint: disable=missing-function-docstring,missing-module-docstring

from conftest import TABLE_PREFIX

import pyodbc


def test_description(cubrid_db_cursor, booze_table):
    cur, _ = cubrid_db_cursor

    assert cur.description is None, \
        'cursor.descripton should be none after executing a'\
        'statement that can return no rows (such as DDL)'

    cur.execute(f'select name from {booze_table}')
    nc = len(cur.description)
    assert nc == 1, f'cursor.description describes {nc} columns'

    assert len(cur.description[0]) in (7, 8), 'cursor.description[x] tuples must have 7 or 8 elements'
    assert cur.description[0][0].lower() == 'name',\
        'cursor.description[x][0] must return column name'
    assert cur.description[0][1] == pyodbc.STRING,\
        f'cursor.description[x][1] must return column type. Got {cur.description[0][1]:r}'

    table_name = f'{TABLE_PREFIX}barflys'
    try:
        # Make sure self.description gets reset
        cur.execute(f'create table {table_name} (name varchar(20))')
        assert cur.description is None,\
            'cursor.description not being set to None when executing '\
            'no-result statments (eg. DDL)'
    finally:
        cur.execute(f'drop table if exists {table_name}')


def _test_description(cubrid_db_cursor, description_table, column_name):
    cur, _ = cubrid_db_cursor
    cur.execute(f"SELECT {column_name} from {description_table}")
    desc = cur.description[0]
    assert desc[0].lower() == column_name.lower()
    assert len(desc) in (7, 8)
    assert desc[1] is not None


def test_description_int(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_int')


def test_description_short(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_short')


def test_description_numeric(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_numeric')


def test_description_float(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_float')


def test_description_double(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_double')


def test_description_monetary(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_monetary')


def test_description_date(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_date')


def test_description_time(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_time')


def test_description_datetime(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_datetime')


def test_description_timestamp(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_timestamp')


def test_description_bit(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_bit')


def test_description_varbit(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_varbit')


def test_description_char(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_char')


def test_description_varchar(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_varchar')


def test_description_string(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_string')


def test_description_set(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_set')


def test_description_multiset(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_multiset')


def test_description_sequence(cubrid_db_cursor, desc_table):
    _test_description(cubrid_db_cursor, desc_table, 'c_sequence')
