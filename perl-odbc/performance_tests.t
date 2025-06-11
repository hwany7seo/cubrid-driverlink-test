#!/usr/bin/perl
use strict;
use warnings;
use Test::More;
use DBI qw(:sql_types);
use Time::HiRes qw(time);
use JSON;
use threads;
use Config::Simple;

my $test_count = 100;
my $iteration_count = 1;

my $dsn = 'DBI:ODBC:CUBRID Driver';
my $user = $ENV{DB_USER} || "dba";
my $password = $ENV{DB_PASSWORD} || "";

sub connect_odbc {
    diag("##################################\n# Connecting to ODBC\n##################################");
    my $dbh = DBI->connect($dsn, $user, $password, { 'RaiseError' => 1, 'PrintError' => 1, 'AutoCommit' => 0 });
    $dbh->{odbc_disable_bind_by_name} = 1;
    $dbh->trace(2, 'odbc_trace.log');

    if (!$dbh) {
        die "Failed to connect to ODBC: $DBI::errstr";
    }
    diag("##################################\n# Connected to ODBC\n##################################");
    return $dbh;
}

sub test_table_insertion_odbc {
    my $dbh = shift;
    diag("##################################\n# ODBC Table Insertion Test\n##################################");
    $dbh->do("DROP TABLE IF EXISTS test_table");
    $dbh->do("CREATE TABLE test_table (id INT, name VARCHAR(255))");
    
    my $start_time = time();
    
    my $sth = $dbh->prepare("INSERT INTO test_table (id, name) VALUES (?, ?)");
    $dbh->{AutoCommit} = 0;
    for my $i (1 .. $test_count) {
        $sth->bind_param(1, $i) or die "Bind failed for ID: " . $sth->errstr;
        $sth->bind_param(2, "perldt$i") or die "Bind failed for Name: " . $sth->errstr;
        print "Binding ID=$i, Name=perldt$i\n";
        $sth->execute() or die "Execute failed: " . $sth->errstr;
    }

    $dbh->commit();
    my $end_time = time();
    my $total_time = $end_time - $start_time;
    
    diag("Inserted $test_count rows in $total_time seconds");
        
}

sub test_data_selection_odbc {
    diag("##################################\n# Test: Data Selection Test\n##################################\n");
    my $dbh = shift;
    my $rows;
    
    my $start_time = time();
    for my $i (1 .. $test_count) {
        my $sth = $dbh->do("SELECT * FROM test_table WHERE id = $i");
        if ($sth) {
            $rows++;
            # diag("id = $i name = $sth");
        }
    }

    my $end_time = time();
    my $total_time = $end_time - $start_time;
    my $avg_time = $total_time / $test_count;

    if ($rows != $test_count) {
        die "Expected $test_count rows, got $rows";
    } else {
        ok(1, "Selected row $rows successfully");
    }    
    diag("Executed $test_count select queries in $total_time seconds (avg: $avg_time sec per query)");
    $dbh->disconnect();
}

for my $i (1 .. $iteration_count) {
    diag("##################################\n# Starting Test Iteration: $i\n##################################\n");
    my $dbh = connect_odbc();
    test_table_insertion_odbc($dbh);
    test_data_selection_odbc($dbh);
}

done_testing();

