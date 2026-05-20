package main

import (
	"database/sql"
	"fmt"
	"log"

	_ "github.com/alexbrainman/odbc"
)

const cubrid_unicode_dns = "DSN=CUBRID_Unicode;UID=dba;PWD=;"

func main() {
	db, err := sql.Open("odbc", cubrid_unicode_dns)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	// Re-create table
	db.Exec("DROP TABLE IF EXISTS tbl_bind_test")
	_, err = db.Exec("CREATE TABLE tbl_bind_test (id INT, a_bit BIT, b_vbit BIT, c_num NUMERIC, d_float FLOAT, e_double DOUBLE, f_date DATE, g_time TIME, g_timest TIMESTAMP, h_set SET, i_bigint BIGINT, j_datetm DATETIME, k_blob BLOB, l_clob CLOB)")
	if err != nil {
		log.Fatal("Create table failed:", err)
	}

	// Test 1: Bind all EXCEPT SET (using literals for SET)
	fmt.Println("Test 1: Binding all columns except SET...")
	query1 := "INSERT INTO tbl_bind_test (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (?, B'1', B'1', ?, ?, ?, ?, ?, ?, {1,2,3}, ?, ?, ?, ?)"
	_, err = db.Exec(query1,
		1,                     // id
		1.1,                   // c_num
		1.1,                   // d_float
		1.1,                   // e_double
		"2026-01-01",          // f_date
		"12:00:00",            // g_time
		"2026-01-01 12:00:00", // g_timest
		100,                   // i_bigint
		"2026-01-01 12:00:00", // j_datetm
		"bind-blob",           // k_blob
		"bind-clob",           // l_clob
	)
	if err != nil {
		fmt.Printf("Test 1 (Basic types) failed: %v\n", err)
	} else {
		fmt.Println("Test 1 (Basic types) passed.")
	}

	fmt.Println("Test 2: Testing BIT binding...")
	query2 := "INSERT INTO tbl_bind_test (id, a_bit, b_vbit, h_set, k_blob, l_clob) VALUES (2, ?, ?, NULL, NULL, NULL)"
	_, err = db.Exec(query2, []byte{0x80}, []byte{0x80}) // B'1', B'1'
	if err != nil {
		fmt.Printf("Test 2 (BIT []byte B'1', B'1') failed: %v\n", err)
		_, err = db.Exec(query2, []byte{0x80}, []byte{0x00}) // B'1', B'0'
		if err != nil {
			fmt.Printf("Test 2 (BIT []byte B'1', B'0') failed: %v\n", err)
		} else {
			fmt.Println("Test 2 (BIT []byte B'1', B'0') passed.")
		}
	} else {
		fmt.Println("Test 2 (BIT []byte B'1', B'1') passed.")
	}

	// Test 3: Test SET binding
	fmt.Println("Test 3: Testing SET binding...")
	query3 := "INSERT INTO tbl_bind_test (id, h_set) VALUES (3, ?)"
	_, err = db.Exec(query3, "{1,2,3}")
	if err != nil {
		fmt.Printf("Test 3 (SET binding) failed: %v\n", err)
	} else {
		fmt.Println("Test 3 (SET binding) passed.")
	}
	
	db.Exec("DROP TABLE tbl_bind_test")
}

