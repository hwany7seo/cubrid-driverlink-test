using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Data.Odbc;
using System.Data.Common;
using System.Diagnostics;
using System.Threading;

namespace adonet_test
{
    class Program
    {
        static void Main(string[] args)
        {
            Console.WriteLine("ADONET ODBC Test Start.");
            ConnectODBC odbc = new ConnectODBC();

            odbc.RunODBCTest();

            Console.WriteLine("ADONET ODBC Test Done.");
        }
    }
}
