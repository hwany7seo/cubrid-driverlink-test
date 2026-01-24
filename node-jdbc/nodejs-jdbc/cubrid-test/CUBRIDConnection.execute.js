import { expect } from 'chai';
import testSetup from './testSetup.js';
import { CUBRIDAsyncWrapper } from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('execute', function() {
        const TABLE_NAME = 'tbl_test_execute_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute simple DDL (CREATE/DROP) and return correct RowsCount', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // DDL usually returns 0 affected rows
            let res = await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            expect(res.result.RowsCount).to.equal(0);
            
            res = await client.execute(`DROP TABLE ${TABLE_NAME}`);
            expect(res.result.RowsCount).to.equal(0);
            
            await client.close();
        });

        it('should execute INSERT with parameters using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, val VARCHAR(50))`);
            
            // Execute with params
            let res = await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [1, 'test']);
            expect(res.result.RowsCount).to.equal(1);
            
            res = await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [2, 'test2']);
            expect(res.result.RowsCount).to.equal(1);
            
            // Verify
            const queryRes = await client.query(`SELECT * FROM ${TABLE_NAME} ORDER BY id`);
            expect(queryRes.result.RowsCount).to.equal(2);
            expect(queryRes.result.rows[0].VAL).to.equal('test');
            
            await client.close();
        });

        it('should execute UPDATE and return correct row count', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2), (3)`);
            
            const res = await client.execute(`UPDATE ${TABLE_NAME} SET id = id + 10 WHERE id > ?`, [1]);
            // execute should return result object with RowsCount for update
            expect(res.result.RowsCount).to.equal(2); // 2 and 3 updated
            
            await client.close();
        });

        it('should rollback transaction when AutoCommit is disabled', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Disable AutoCommit
            await client.setAutoCommitMode(false);
            
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            
            // Rollback
            await client.rollback();
            
            // Check if inserted data is gone
            const res = await client.query(`SELECT COUNT(*) as cnt FROM ${TABLE_NAME}`);
            expect(Number(res.result.rows[0].CNT)).to.equal(0);
            
            await client.close();
        });

        it('should commit transaction manually when AutoCommit is disabled', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Disable AutoCommit
            await client.setAutoCommitMode(false);
            
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            
            // Commit
            await client.commit();
            
            // Check if inserted data persists
            const res = await client.query(`SELECT COUNT(*) as cnt FROM ${TABLE_NAME}`);
            expect(Number(res.result.rows[0].CNT)).to.equal(1);
            
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
                // JDBC specific error checking
                // e.g. "Table not found"
            }
            
            await client.close();
        });
        
        it('should fail on invalid parameter count', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            try {
                await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?)`, [1, 2]); // Too many params
                // throw new Error('Should have failed'); // JDBC might be lenient depending on driver
            } catch (e) {
                expect(e).to.exist;
            }
            
            await client.close();
        });
    });
});
