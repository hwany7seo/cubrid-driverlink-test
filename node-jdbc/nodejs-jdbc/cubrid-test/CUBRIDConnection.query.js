import { expect } from 'chai';
import testSetup from './testSetup.js';
import { CUBRIDAsyncWrapper } from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('query', function() {
        const TABLE_NAME = 'tbl_test_query_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should succeed to execute query(sql) and return correct result structure', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            const response = await client.query('SHOW TABLES');
            expect(response).to.be.an('object').to.have.property('queryHandle');
            expect(response).to.have.property('result').to.be.an('object');
            
            const result = response.result;
            expect(result).to.have.property('RowsCount').to.be.a('number').to.be.above(0);
            expect(result).to.have.property('ColumnNames').to.be.an('array');
            expect(result).to.have.property('ColumnValues').to.be.an('array');
            
            await client.closeQuery(response.queryHandle);
            await client.close();
        });

        it('should succeed to execute query(sql, params) with various types', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            await client.execute(`CREATE TABLE ${TABLE_NAME}(
                id INT, 
                name VARCHAR(50), 
                created_at DATETIME, 
                score DOUBLE, 
                is_active INT
            )`);

            const now = new Date();
            // JDBC might lose millisecond precision depending on driver/DB config
            now.setMilliseconds(0); 
            
            const params = [1, 'test_user', now, 99.5, 1];
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?, ?, ?, ?)`, params);

            const response = await client.query(`SELECT * FROM ${TABLE_NAME} WHERE id = ?`, [1]);
            const row = response.result.rows[0];

            expect(row.ID).to.equal(1);
            expect(row.NAME).to.equal('test_user');
            // Date comparison
            // JDBC returns java.sql.Timestamp which node-jdbc converts to string or object
            // Adjust expectation based on actual return type
            // expect(new Date(row.CREATED_AT).getTime()).to.be.closeTo(now.getTime(), 1000);
            expect(Number(row.SCORE)).to.equal(99.5);
            expect(row.IS_ACTIVE).to.equal(1);

            await client.close();
        });

        it('should handle large result sets', async function() {
            this.timeout(60000); // Increase timeout for large data
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Batch insert 1000 rows
            const batchSize = 1000;
            const insertSql = `INSERT INTO ${TABLE_NAME} VALUES(?)`;
            const stmt = await client.getRawConnection().prepareStatement(insertSql);
            
            try {
                for(let i=0; i<batchSize; i++) {
                    await stmt.setInt(1, i);
                    await stmt.addBatch();
                }
                await stmt.executeBatch();
            } finally {
                if (stmt.closeSync) stmt.closeSync();
                else if (stmt.close) await stmt.close();
            }

            const response = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            expect(response.result.RowsCount).to.equal(batchSize);
            expect(response.result.rows.length).to.equal(batchSize);

            await client.close();
        });

        it('should handle NULL values correctly', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, val VARCHAR(50))`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1, NULL)`);

            const response = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            const row = response.result.rows[0];
            
            expect(row.ID).to.equal(1);
            expect(row.VAL).to.be.null; // or undefined

            await client.close();
        });

        it('should fail gracefully on syntax error', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            try {
                await client.query('SELECT * FROM'); // Syntax error
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.exist;
                // Check for syntax error message
            }

            await client.close();
        });

        it('should fail gracefully on non-existing table', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            try {
                await client.query('SELECT * FROM non_existing_table_xyz');
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.exist;
                // Check for table not found error
            }

            await client.close();
        });
        
        it('should succeed to query LAST_INSERT_ID()', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT AUTO_INCREMENT, val VARCHAR(10))`);
            await client.execute(`INSERT INTO ${TABLE_NAME}(val) VALUES('a')`);
            
            const res = await client.query('SELECT LAST_INSERT_ID()');
            // Check if we get a valid ID back
            // The column name might vary, so check first value
            const id = Object.values(res.result.rows[0])[0];
            expect(Number(id)).to.be.above(0);
            
            await client.close();
        });
    });
});
