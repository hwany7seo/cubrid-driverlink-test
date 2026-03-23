require 'test/unit'
require 'odbc'

class CUBRID_Test < Test::Unit::TestCase
  def setup
    @con = ODBC.connect('CUBRID_Unicode', 'dba', '')
  end

  def test_connect
    @con.autocommit = true
    connection_info = @con.get_info(ODBC::SQL_DATA_SOURCE_NAME) || @con.to_s
    puts connection_info
    assert(connection_info, 'Connect failed.')
  end

  def test_query
    @con.run('drop table if exists test_cubrid')
    @con.run('create table test_cubrid (a int, b double, c string, d date)')

    # DATE 컬럼: ODBC::Date는 SQL_C_DATE로 바인딩된다. CUBRID ODBC가 SQLDescribeParam을
    # 제대로 주지 않으면 ruby-odbc는 파라미터 SQL 타입을 VARCHAR로 가정하고, C 타입 DATE와
    # 맞지 않아 실행 시 [-20008] Type conversion이 난다(드라이버/메타데이터 쪽 이슈).
    # 여기서는 문자열 리터럴 값으로 바인딩해 서버가 DATE로 변환하도록 한다.
    @con.prepare('insert into test_cubrid values (?, ?, ?, ?)') do |stmt|
      stmt.execute(10, 3.141592, 'hello', '2007-12-25')
    end

    stmt = @con.run('SELECT * FROM test_cubrid')
    while (row = stmt.fetch)
      print row[0], ' ', row[1], ' ', row[2], ' ', row[3]
      puts
    end
    stmt.drop
  end

  def test_column_info
    @con.prepare('SELECT * FROM db_user') do |stmt|
      stmt.execute
      stmt.columns.each do |_key, col|
        print col.name, ' '
        print col.type, ' ' # ODBC SQL type code (ruby-odbc: ODBC::Column has no type_name)
        print col.precision, ' '
        print col.scale, ' '
        print col.nullable
        puts
      end
    end
  end

  def test_each
    @con.prepare('SELECT * FROM db_user') do |stmt|
      stmt.execute
      stmt.each do |r|
        print r[0]
        puts
      end
    end
  end

  def test_each_hash
    @con.prepare('SELECT * FROM db_user') do |stmt|
      stmt.execute
      stmt.each_hash do |r|
        print(r['name'] || r['NAME'])
        puts
      end
    end
  end

  def test_affected_rows
    @con.run('drop table if exists test_cubrid2')
    @con.run('create table test_cubrid2 (a int)')

    ins = @con.prepare('insert into test_cubrid2 values (?)')
    ins.execute(100)
    ins.execute(101)
    ins.drop

    stmt = @con.run('SELECT * FROM test_cubrid2')
    rows = stmt.fetch_all
    assert_equal(2, rows.size)
    stmt.drop

    @con.run('drop table if exists test_cubrid2')
  end

  def teardown
    @con.disconnect if @con&.connected?
  rescue StandardError
    # ignore teardown errors
  end
end
