package main

import (
	"database/sql"
	"fmt"
	"log"
	"time"

	_ "github.com/alexbrainman/odbc"
)

const insert_count = 100

func main() {
	db, err := sql.Open("odbc", "Driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;db_name=demodb;charset=utf-8;autocommit=0;")
	if err != nil {
		log.Fatal(err)
	} else {
		fmt.Println("Connected")
	}

	_, err = db.Exec("DROP TABLE IF EXISTS test_table")
	if err != nil {
		log.Fatal(err)
	} else {
		fmt.Println("Dropped table")
	}

	_, err = db.Exec("CREATE TABLE test_table (id INT, name VARCHAR(20))")
	if err != nil {
		log.Fatal(err)
	} else {
		fmt.Println("Created table")
	}

	start_time := time.Now()
	tx, err := db.Begin()
	if err != nil {
		log.Fatal(err)
	}

	// insert_stmt, err := tx.Prepare("INSERT INTO test_table (id, name) VALUES (?, ?)")
	// if err != nil {
	// 	tx.Rollback()
	// 	log.Fatal(err)
	// }

	// for i := 0; i < insert_count; i++ {
	// 	name := fmt.Sprintf("godata%d", i)
	// 	//fmt.Printf("insert %d, name: %s\n", i, name)

	// 	_, err := insert_stmt.Exec(i, name)
	// 	if err != nil {
	// 		tx.Rollback()
	// 		log.Fatal(err)
	// 	}

	// }

	for i := 0; i < insert_count; i++ {
		sql := fmt.Sprintf("INSERT INTO test_table (id, name) VALUES (%d, 'godata%d')", i, i)
		_, err := tx.Exec(sql)
		if err != nil {
			tx.Rollback()
			log.Fatal(err)
		}
	}

	tx.Commit()
	if err != nil {
		log.Fatal(err)
	}
	end_time := time.Now()
	fmt.Printf("insert %d	rows total time: %.4f (s)\n", insert_count, end_time.Sub(start_time).Seconds())

	select_count_stmt, err := db.Prepare("SELECT count(*) FROM test_table")
	if err != nil {
		log.Fatal(err)
	}

	var count int
	err = select_count_stmt.QueryRow().Scan(&count)
	if err != nil {
		log.Fatal(err)
	}
	fmt.Printf("selected Count(*): %d\n", count)

	start_time2 := time.Now()
	select_count := 0
	select_stmt, err := db.Prepare("SELECT * FROM test_table where id = ?")
	if err != nil {
		log.Fatal(err)
	}

	for i := 0; i < insert_count; i++ {
		rows, err := select_stmt.Query(i)
		if err != nil {
			log.Fatal(err)
		}

		for rows.Next() {
			var id int
			var name string
			err = rows.Scan(&id, &name)
			if err != nil {
				log.Fatal(err)
			}
			//fmt.Printf("ID: %d, Name: %s\n", id, name)
			select_count++
		}

		if err := rows.Err(); err != nil {
			log.Fatal(err)
		}
		rows.Close()
	}
	end_time2 := time.Now()
	fmt.Printf("select count: %d rows total time: %.4f (s)\n", select_count, end_time2.Sub(start_time2).Seconds())
}
