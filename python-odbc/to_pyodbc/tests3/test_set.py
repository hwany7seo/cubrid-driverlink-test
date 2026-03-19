import pyodbc
# pylint: disable=missing-function-docstring,missing-module-docstring
from conftest import TABLE_PREFIX


def _literal_set_int(tup):
    return '{' + ','.join(str(int(x)) for x in tup) + '}'


def _literal_set_str(tup):
    parts = []
    for x in tup:
        s = str(x).replace("'", "''")
        parts.append(f"'{s}'")
    return '{' + ','.join(parts) + '}'


def _cell_as_strset(val):
    """Normalize pyodbc set column (Python set or '{a, b}' string) to frozenset of strings."""
    if isinstance(val, set):
        return frozenset(val)
    s = str(val).strip()
    assert s.startswith('{') and s.endswith('}')
    inner = s[1:-1].strip()
    if not inner:
        return frozenset()
    return frozenset(x.strip() for x in inner.split(','))


def _assert_set_row(actual_row, expected_tuple_of_sets):
    assert len(actual_row) == len(expected_tuple_of_sets)
    for cell, expected in zip(actual_row, expected_tuple_of_sets):
        assert _cell_as_strset(cell) == frozenset(expected)


def _literal_set_bit(tup):
    # CUBRID accepts 0x-prefixed hex for bit data in set literals
    parts = []
    for b in tup:
        hx = b.hex().upper()
        parts.append(f"X'{hx}'")
    return '{' + ','.join(parts) + '}'


def _test_set_literal(cur, columns_sql, values_sql_fragment):
    """Insert using CUBRID set/multiset literals — ODBC does not bind Python tuples to set columns."""
    table_name = f'{TABLE_PREFIX}set_prepare'
    cur.execute(f'drop table if exists {table_name}')
    try:
        cur.execute(f'create table if not exists {table_name} ({columns_sql})')
        cur.execute(f'insert into {table_name} values ({values_sql_fragment})')
        assert cur.rowcount in (-1, 1)

        cur.execute(f'select * from {table_name}')
        return cur.fetchall()
    finally:
        cur.execute(f'drop table if exists {table_name}')

def _test_set_prepare(cur, columns_sql, samples, sample_size):
    table_name = f'{TABLE_PREFIX}set_prepare'
    cur.execute(f'drop table if exists {table_name}')
    try:
        placeholders = ",".join(["?"] * sample_size)
        cur.execute(f"create table if not exists {table_name} ({columns_sql})")
        cur.executemany(f"insert into {table_name} values ({placeholders})", samples)
        assert cur.rowcount == 1

        cur.execute(f"select * from {table_name}")
        return cur.fetchall()
    finally:
        cur.execute(f'drop table if exists {table_name}')


def test_set_prepare_int(cubrid_db_cursor):
    samples = [((1, 2, 3, 4), (5, 6, 7))]
    row = samples[0]
    frag = f'{_literal_set_int(row[0])}, {_literal_set_int(row[1])}'
    inserted = _test_set_literal(cubrid_db_cursor[0],
        'col_1 set of int, col_2 set of int', frag)
    assert len(inserted) == 1
    _assert_set_row(inserted[0], ({'1', '2', '3', '4'}, {'5', '6', '7'}))


def test_set_prepare_char_int(cubrid_db_cursor):
    samples = [(('a', 'b', 'c', 'd'), (5, 6, 7))]
    row = samples[0]
    frag = f'{_literal_set_str(row[0])}, {_literal_set_int(row[1])}'
    inserted = _test_set_literal(cubrid_db_cursor[0],
        'col_1 set of char, col_2 set of int', frag)
    assert len(inserted) == 1
    _assert_set_row(inserted[0], ({'a', 'b', 'c', 'd'}, {'5', '6', '7'}))


def test_set_prepare_char(cubrid_db_cursor):
    samples = [(('a', 'b', 'c', 'd'),)]
    frag = _literal_set_str(samples[0][0])
    inserted = _test_set_literal(cubrid_db_cursor[0], 'col_1 set of char', frag)
    assert len(inserted) == 1
    _assert_set_row(inserted[0], ({'a', 'b', 'c', 'd'},))


def test_set_prepare_string(cubrid_db_cursor):
    samples = [(('abc', 'bcd', 'ceee', 'dddddd'),)]
    frag = _literal_set_str(samples[0][0])
    inserted = _test_set_literal(cubrid_db_cursor[0], 'col_1 set of varchar', frag)
    assert len(inserted) == 1
    _assert_set_row(inserted[0], ({'abc', 'bcd', 'ceee', 'dddddd'},))


def test_set_prepare_bit(cubrid_db_cursor):
    samples = [((b'\x14', b'\x12\x90'),)]
    frag = _literal_set_bit(samples[0][0])
    inserted = _test_set_literal(cubrid_db_cursor[0], 'col_1 set of bit(16)', frag)
    assert len(inserted) == 1
    _assert_set_row(inserted[0], ({'1400', '1290'},))
