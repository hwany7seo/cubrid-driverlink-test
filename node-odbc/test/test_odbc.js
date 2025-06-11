import odbc from 'odbc'

import assert from 'assert';
import fs from "node:fs";
import {expect} from "chai";

const stressLoad = 100

// Define your database connection details
const connectionString = "driver={CUBRID Driver};server=192.168.2.32;port=33000;uid=dba;pwd=;db_name=demodb;"

let connection;
let statement;

// Mocha test suite for performance testing
describe('ODBC Performance Tests', function() {

    this.timeout(100000);

    before(async function() {
        // Set up the connection before tests start
        connection = await odbc.connect(connectionString);
        statement = await connection.createStatement();
        console.log('Connected to the database!');

        // Create the test table if it does not exist
        const createTableQuery = `
      CREATE TABLE IF NOT EXISTS test_table (
        id INT,
        name VARCHAR(100)
      );
    `;
        await connection.query('DROP TABLE IF EXISTS test_table');
        await connection.query(createTableQuery);

    });

    after(async function() {
        // Clean up after tests (drop the table)

        await connection.close();
        console.log('Connection closed.');
    });

    it('insert work', async function() {
        await connection.beginTransaction();

        const start = Date.now();
        await statement.prepare(`INSERT INTO test_table (id, name) VALUES (?, ?)`);
        
        for (let i = 0; i < stressLoad; i++) {
            await statement.bind([i, `nododb${i}`]);
            await statement.execute();
        }
        await connection.commit();
        const end = Date.now();
        console.log(`insert work count: ${stressLoad} elapsed time: ${end - start}ms`);
    });

    it('selected Count work', async function() {
        const rows = await connection.query('SELECT count(*) FROM test_table');
        console.log("result count", rows[0]);
    });

    it('selected work', async function() {
        const start = Date.now();
        let count = 0;
        for (let i = 0; i < stressLoad; i++) {
            const result = await connection.query(`SELECT *
                                            FROM test_table WHERE id = ?`, [i]);
            if (result && result.length > 0) {
                count += 1;
            }
        }
        const end = Date.now();
        console.log(`selected work count: ${count} elapsed time: ${end - start}ms`);
    });
});
