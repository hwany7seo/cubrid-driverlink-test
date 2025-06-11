using System;
using System.Collections.Generic;
using System.Data.Odbc;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Configuration;
using System.Runtime.InteropServices;

namespace adonet_test
{
    class ConnectODBC
    {
        StringBuilder result = new StringBuilder();
        int insertCount = 100;

        public void RunODBCTest()
        {
            Console.WriteLine("================== ODBC_RunODBCTest ================================");
            OdbcConnection conn = ODBC_Connect();
            ODBC_Create_Table(conn);
            ODBC_Insert_Test(conn);
            ODBC_Select(conn);
            conn.Close();
        }

        public OdbcConnection ODBC_Connect()
        {
            Console.WriteLine("ODBC_Connect");
            string connectionString = "Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;db_name=demodb;charset=utf-8;autocommit=0;";
            OdbcConnection conn = new OdbcConnection(connectionString);
            conn.Open();
            Console.WriteLine("ODBC_Connect success");
            return conn;
        }

        public void ODBC_Select(OdbcConnection conn)
        {
            long totalTime = 0L;
            int insertedCount = 0;
            Stopwatch watch = new Stopwatch();

            using (OdbcCommand cmd = new OdbcCommand("select count(*) from test_table;", conn))
            {
                using (OdbcDataReader reader = cmd.ExecuteReader())
                {
                    while (reader.Read())
                    {
                        Console.WriteLine("ODBC count(*) query: " + reader[0]);
                    }
                }
                watch.Start();

                cmd.CommandText = "select * from test_table where id = ?;";
                cmd.Parameters.Add(new OdbcParameter("?", 1));
                cmd.Prepare();

                for (int count = 1; count <= insertCount; count++)
                {
                    cmd.Parameters[0].Value = count;
                    using (OdbcDataReader reader = cmd.ExecuteReader())
                    {
                        while (reader.Read())
                        {
                            insertedCount++;
                        }
                    }
                }
                watch.Stop();
                totalTime += watch.ElapsedMilliseconds;
            }


            Console.WriteLine("ODBC select query count: " + insertedCount + " totalTime: " + totalTime * 0.001 + "s");
        }

        public void ODBC_Create_Table(OdbcConnection conn)
        {
            Console.WriteLine("ODBC_Create_Table");

            Stopwatch watch = new Stopwatch();

            watch.Start();

            using (OdbcCommand cmd = new OdbcCommand(null, conn))
            {
                cmd.CommandText = "Drop table if exists test_table;";
                cmd.ExecuteNonQuery();

                cmd.CommandText = "create table test_table(id integer, name varchar(255));";
                cmd.ExecuteNonQuery();
            }

        }

        public void ODBC_Insert_Test(OdbcConnection conn)
        {
            long RunTime = 0L;
            Stopwatch watch = new Stopwatch();
            watch.Start();

            using (OdbcTransaction transaction = conn.BeginTransaction())
            {
                try
                {
                    string sql = "insert into test_table values(?, ?);";
                    using (OdbcCommand cmd = new OdbcCommand(sql, conn, transaction))
                    {
                        cmd.Prepare();

                        cmd.Parameters.Add("param1", OdbcType.Int);
                        cmd.Parameters.Add("param2", OdbcType.VarChar);

                        for (int count = 1; count <= insertCount; count++)
                        {
                            cmd.Parameters["param1"].Value = count;
                            cmd.Parameters["param2"].Value = "adoodb" + count;
                            cmd.ExecuteNonQuery();
                        }
                        transaction.Commit();
                    }
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"Error occurred: {ex.Message}");
                    try
                    {
                        transaction.Rollback();
                    }
                    catch (Exception rollbackEx)
                    {
                        Console.WriteLine($"Rollback failed: {rollbackEx.Message}");
                    }
                    throw;
                }
            }

            watch.Stop();
            RunTime = watch.ElapsedMilliseconds;
            Console.WriteLine($"ODBC Insert {insertCount} record run time: {RunTime * 0.001}s");
        }

    }
}
