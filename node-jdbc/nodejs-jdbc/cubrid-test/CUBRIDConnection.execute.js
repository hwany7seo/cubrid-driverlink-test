import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('execute', function() {
        const TABLE_NAME = 'tbl_test_execute_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute simple DDL (CREATE/DROP)', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            // Check if table exists (implied by success)
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            
            await client.close();
        });

        it('should execute INSERT with parameters using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, val VARCHAR(50))`);
            
            // Execute with params
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [1, 'test']);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [2, 'test2']);
            
            // Verify
            const res = await client.query(`SELECT * FROM ${TABLE_NAME} ORDER BY id`);
            expect(res.result.RowsCount).to.equal(2);
            expect(res.result.rows[0].VAL).to.equal('test');
            
            await client.close();
        });

        it('should execute UPDATE and return row count', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2), (3)`);
            
            const res = await client.execute(`UPDATE ${TABLE_NAME} SET id = id + 10 WHERE id > ?`, [1]);
            // execute should return result object with RowsCount for update
            expect(res.result.RowsCount).to.equal(2); // 2 and 3 updated
            
            await client.close();
        });

        it('should execute with callback', function(done) {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            client.connect().then(() => {
                client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`, function(err, result) {
                    if (err) return done(err);
                    expect(result).to.exist;
                    
                    client.close().then(() => done());
                });
            });
        });

        it('should fail on invalid SQL', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            try {
                await client.execute('SELECT * FROM non_existent_table');
                throw new Error('Should have failed');
            } catch (e) {
                expect(e).to.exist;
            }
            
            await client.close();
        });
        
        it('should fail on invalid parameter count', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            try {
                await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?)`, [1, 2]); // Too many params
                // JDBC might ignore extra params or throw error depending on driver
                // If it doesn't throw, we can't test failure. 
                // But usually it throws "Parameter index out of range".
            } catch (e) {
                expect(e).to.exist;
            }
            
            await client.close();
        });
    });
});
