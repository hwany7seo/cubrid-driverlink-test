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

	var year sql.NullInt32
	var city sql.NullString
	var open_date sql.NullTime

	rows, err := db.Query("select host_year, host_city, opening_date from olympic")

	if err != nil {
		log.Fatal(err)
	}

	defer rows.Close()

	for rows.Next() {
		err := rows.Scan(&year, &city, &open_date)
		if err != nil {
			log.Fatal(err)
		}

		if year.Valid {
			fmt.Print(year.Int32, "\t")
		}

		if city.Valid {
			fmt.Print(city.String, "\t")
			if len(city.String) < 8 {
				fmt.Print("\t")
			}
		}

		if open_date.Valid {
			fmt.Println(open_date.Time)
		}
	}
}
