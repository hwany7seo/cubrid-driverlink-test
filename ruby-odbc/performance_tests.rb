#!/usr/bin/env ruby
require 'json'
require 'time'
require 'inifile'
require 'dbi'
require 'dbd/odbc'
require 'odbc_utf8'

ITERATIONS = 100

# Fixnum aliasing for Ruby 2.4+
unless defined?(Fixnum)
  Fixnum = Integer
end 

dsn = 'CUBRID Driver Unicode'
dbi_dsn = 'DBI:ODBC:CUBRID Driver Unicode'
user = 'dba'
password = ''

def test_table_insertion_dbi_odbc(dbi_dsn, user, password) 
  puts "##################################\n# Test (DBI ODBC): Table Insertion Test\n##################################\n" 
  iterations = ITERATIONS 
  dbh = DBI.connect(dbi_dsn, user, password, { 'AutoCommit' => false }) 
  dbh.do("DROP TABLE IF EXISTS test_table") 
  dbh.do("CREATE TABLE test_table (id INT, name VARCHAR(255))") 

  start_time = Time.now 
  dbh.begin rescue nil
  sth = dbh.prepare("INSERT INTO test_table (id, name) VALUES (?, ?)") 
  raise "DBI.prepare failed" if sth.nil? 
  iterations.times do |i| 
    sth.execute(i + 1, "ruodbc#{i+1}") 
  end 
  dbh.commit 
  end_time = Time.now 
  total_time = end_time - start_time 
  avg_time = total_time / iterations.to_f 

  puts "Inserted #{iterations} rows in #{total_time} seconds (avg: #{avg_time} sec per insert)" 
  sth.finish if sth.respond_to?(:finish)
  dbh.disconnect if dbh.respond_to?(:disconnect)
end

def test_data_selection_dbi_odbc(dbi_dsn, user, password)
  puts "##################################\n# Test (DBI ODBC): Data Selection Test\n##################################\n"
  iterations = ITERATIONS
  dbh = DBI.connect(dbi_dsn, user, password, { 'AutoCommit' => true })
  
  stmt = dbh.prepare("Select count(*) from test_table")
  stmt.execute
  row = stmt.fetch
  stmt.finish if stmt.respond_to?(:finish)
  puts "SelectedRow count: #{row[0]}"

  stmt = dbh.prepare("SELECT * FROM test_table")
  stmt.execute
  rows = stmt.fetch_all
  stmt.finish if stmt.respond_to?(:finish)
  puts "Data all selected. rowCount: #{rows.size}"
  for i in 0..rows.size-1
    row = rows[i]
    # puts "Row1 #{i}: ID=#{row[0]}, Name=#{row[1]}"
  end

  start_time = Time.now
  sel_stmt = dbh.prepare("SELECT * FROM test_table WHERE id = ?")
  iterations.times do |i|
    sel_stmt.execute(i + 1)
    rows = sel_stmt.fetch_all
  end
  sel_stmt.finish if sel_stmt.respond_to?(:finish)
  end_time = Time.now
  total_time = end_time - start_time
  avg_time = total_time / iterations.to_f
  
  puts "Executed #{iterations} select queries in #{total_time} seconds (avg: #{avg_time} sec per query)"
  dbh.disconnect if dbh.respond_to?(:disconnect)
end

def test_table_insertion_odbc(dsn, user, password)
  puts "##################################\n# Test (ODBC): Table Insertion Test\n##################################\n"
  iterations = ITERATIONS
  # ODBC::Database.autocommit = false
  # dbh = ODBC::Database.connect(dsn, user, password)
  dbh = ODBC::Database.new
  dbh.connect(dsn, user, password)
  dbh.autocommit = 0
  
  dbh.do("DROP TABLE IF EXISTS test_table")
  dbh.do("CREATE TABLE test_table (id INT, name VARCHAR(255))")
  
  start_time = Time.now

  sth = dbh.prepare("INSERT INTO test_table (id, name) VALUES (?, ?)")
  raise "DBI.prepare failed" if sth.nil?
  iterations.times do |i|
    sth.execute(i + 1, "rubyodb#{i+1}")
  end
  dbh.commit
  end_time = Time.now
  total_time = end_time - start_time
  avg_time = total_time / iterations.to_f
  
  puts "Inserted #{iterations} rows in #{total_time} seconds (avg: #{avg_time} sec per insert)"
  sth.finish if sth.respond_to?(:finish)
  dbh.disconnect if dbh.respond_to?(:disconnect)
end

def test_data_selection_odbc(dsn, user, password)
  puts "##################################\n# Test (ODBC): Data Selection Test\n##################################\n"
  iterations = ITERATIONS
  dbh = ODBC::Database.new
  dbh.connect(dsn, user, password)
  dbh.autocommit = 0
  
  stmt = dbh.prepare("Select count(*) from test_table")
  stmt.execute
  row = stmt.fetch
  stmt.finish if stmt.respond_to?(:finish)
  puts "SelectedRow count: #{row[0]}"

  stmt = dbh.prepare("SELECT * FROM test_table")
  stmt.execute
  rows = stmt.fetch_all
  stmt.finish if stmt.respond_to?(:finish)
  puts "Data all selected. rowCount: #{rows.size}"
  for i in 0..rows.size-1
    row = rows[i]
    # puts "Row1 #{i}: ID=#{row[0]}, Name=#{row[1]}"
  end

  start_time = Time.now
  sel_stmt = dbh.prepare("SELECT * FROM test_table WHERE id = ?")
  iterations.times do |i|
    sel_stmt.execute(i + 1)
    rows = sel_stmt.fetch_all
  end
  sel_stmt.finish if sel_stmt.respond_to?(:finish)
  end_time = Time.now
  total_time = end_time - start_time
  avg_time = total_time / iterations.to_f
  
  puts "Executed #{iterations} select queries in #{total_time} seconds (avg: #{avg_time} sec per query)"
  dbh.disconnect if dbh.respond_to?(:disconnect)
end

# ruby-odbc
test_table_insertion_odbc(dsn, user, password)
test_data_selection_odbc(dsn, user, password)

# DBI(with DBD:ODBC)
test_table_insertion_dbi_odbc(dbi_dsn, user, password)
test_data_selection_dbi_odbc(dbi_dsn, user, password)


