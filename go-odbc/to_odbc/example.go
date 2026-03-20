package main

import (
	"database/sql"
	"fmt"
	"log"

	_ "github.com/alexbrainman/odbc"
)

const cubrid_dns = "DSN=CUBRID Driver;UID=dba;PWD=;"
const cubrid_unicode_dns = "DSN=CUBRID Driver Unicode;UID=dba;PWD=;"

func main() {
	db, err := sql.Open("odbc", cubrid_unicode_dns)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	create_table_and_insert_data(db)

	var id sql.NullInt64
	var a_bit sql.NullString
	var b_vbit sql.NullString
	var c_num sql.NullFloat64
	var d_float sql.NullFloat64
	var e_double sql.NullFloat64
	var f_date sql.NullTime
	var g_time sql.NullTime
	var g_timest sql.NullTime
	var h_set sql.NullString
	var i_bigint sql.NullInt64
	var j_datetm sql.NullTime
	var k_blob sql.NullString
	var l_clob sql.NullString

	rows, err := db.Query("select * from tbl_go")

	if err != nil {
		log.Fatal(err)
	}

	defer rows.Close()

	for rows.Next() {
		err := rows.Scan(&id, &a_bit, &b_vbit, &c_num, &d_float, &e_double,
			&f_date, &g_time, &g_timest, &h_set, &i_bigint, &j_datetm, &k_blob, &l_clob)
		if err != nil {
			log.Fatal(err)
		}

		fmt.Println("--------example.go---------")
		fmt.Println("id: ", id)
		fmt.Println("a_bit: ", a_bit)
		fmt.Println("b_vbit: ", b_vbit)
		fmt.Println("c_num: ", c_num)
		fmt.Println("d_float: ", d_float)
		fmt.Println("e_double: ", e_double)
		fmt.Println("f_date: ", f_date)
		fmt.Println("g_time: ", g_time)
		fmt.Println("g_timest: ", g_timest)
		fmt.Println("h_set: ", h_set)
		fmt.Println("i_bigint: ", i_bigint)
		fmt.Println("j_datetm: ", j_datetm)
		fmt.Println("k_blob: ", k_blob)
		fmt.Println("l_clob: ", l_clob)
		fmt.Println("--------------------------------")
	}
	if err := rows.Err(); err != nil {
		log.Fatal(err)
	}

	// drop_table(db)
}

func create_table_and_insert_data(db *sql.DB) {
	if _, err := db.Exec("DROP TABLE IF EXISTS tbl_go"); err != nil {
		log.Println("Drop table failed:", err)
	}
	if _, err := db.Exec("CREATE TABLE tbl_go (id INT, a_bit BIT, b_vbit BIT, c_num NUMERIC, d_float FLOAT, e_double DOUBLE, f_date DATE, g_time TIME, g_timest TIMESTAMP, h_set SET, i_bigint BIGINT, j_datetm DATETIME, k_blob BLOB, l_clob CLOB)"); err != nil {
		log.Println("Create table failed:", err)
	}

	// Insert data using literals for BIT and functions for BLOB/CLOB.
	// Note: h_set is set to NULL because CUBRID ODBC driver interprets '{...}' literals as escape sequences,
	// and parameter binding for SET columns may not be fully supported in this environment.
	insertQueries := []string{
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (1, B'1', B'1', 1.0, 1.0, 1.0, '2026-01-01', '12:00:00', '2026-01-01 12:00:00', NULL, 1, '2026-01-01 12:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (2, B'0', B'0', 2.0, 2.0, 2.0, '2026-01-02', '13:00:00', '2026-01-02 13:00:00', NULL, 2, '2026-01-02 13:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (3, B'1', B'1', 3.0, 3.0, 3.0, '2026-01-03', '14:00:00', '2026-01-03 14:00:00', NULL, 3, '2026-01-03 14:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (4, B'0', B'0', 4.0, 4.0, 4.0, '2026-01-04', '15:00:00', '2026-01-04 15:00:00', NULL, 4, '2026-01-04 15:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (5, B'1', B'1', 5.0, 5.0, 5.0, '2026-01-05', '16:00:00', '2026-01-05 16:00:00', NULL, 5, '2026-01-05 16:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (6, B'0', B'0', 6.0, 6.0, 6.0, '2026-01-06', '17:00:00', '2026-01-06 17:00:00', NULL, 6, '2026-01-06 17:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (7, B'1', B'1', 7.0, 7.0, 7.0, '2026-01-07', '18:00:00', '2026-01-07 18:00:00', NULL, 7, '2026-01-07 18:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (8, B'0', B'0', 8.0, 8.0, 8.0, '2026-01-08', '19:00:00', '2026-01-08 19:00:00', NULL, 8, '2026-01-08 19:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (9, B'1', B'1', 9.0, 9.0, 9.0, '2026-01-09', '20:00:00', '2026-01-09 20:00:00', NULL, 9, '2026-01-09 20:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
		"INSERT INTO tbl_go (id, a_bit, b_vbit, c_num, d_float, e_double, f_date, g_time, g_timest, h_set, i_bigint, j_datetm, k_blob, l_clob) VALUES (10, B'0', B'0', 10.0, 10.0, 10.0, '2026-01-10', '21:00:00', '2026-01-10 21:00:00', NULL, 10, '2026-01-10 21:00:00', CHAR_TO_BLOB('blob'), CHAR_TO_CLOB('clob'))",
	}

	for i, query := range insertQueries {
		if _, err := db.Exec(query); err != nil {
			log.Printf("Insert %d failed: %v", i+1, err)
		}
	}
}

func drop_table(db *sql.DB) {
	db.Exec("DROP TABLE tbl_go")
}
