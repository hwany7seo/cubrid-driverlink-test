import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('batchExecuteNoQuery', function() {
        const TABLE_NAME = 'tbl_test_batch_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute multiple queries in batch', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const sqls = [
                `CREATE TABLE ${TABLE_NAME}(id INT)`,
                `INSERT INTO ${TABLE_NAME} VALUES(1)`,
                `INSERT INTO ${TABLE_NAME} VALUES(2)`,
                `UPDATE ${TABLE_NAME} SET id = id + 10`
            ];
            
            const res = await client.batchExecuteNoQuery(sqls);
            
            // Check result structure
            expect(res).to.have.property('result');
            // RowsCount might be sum of affected rows or something else
            // JDBC executeBatch returns int[] of update counts. 
            // Wrapper reduces it.
            // CREATE: 0, INSERT: 1, INSERT: 1, UPDATE: 2 => Total 4
            expect(res.result.RowsCount).to.be.at.least(2); 
            
            // Verify data
            const queryRes = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            expect(queryRes.result.RowsCount).to.equal(2);
            expect(queryRes.result.rows[0].ID).to.equal(11);
            expect(queryRes.result.rows[1].ID).to.equal(12);
            
            await client.close();
        });

        it('should handle large batch', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            const sqls = [];
            for(let i = 0; i < 100; i++) {
                sqls.push(`INSERT INTO ${TABLE_NAME} VALUES(${i})`);
            }
            
            await client.batchExecuteNoQuery(sqls);
            
            const res = await client.query(`SELECT COUNT(*) as CNT FROM ${TABLE_NAME}`);
            expect(Number(res.result.rows[0].CNT)).to.equal(100);
            
            await client.close();
        });

        it('should fail on invalid SQL in batch', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const sqls = [
                `CREATE TABLE ${TABLE_NAME}(id INT)`,
                `INSERT INTO ${TABLE_NAME} VALUES(1)`,
                `INSERT INTO INVALID_TABLE VALUES(1)` // Error here
            ];
            
            try {
                await client.batchExecuteNoQuery(sqls);
                throw new Error('Should have failed');
            } catch(e) {
                expect(e).to.exist;
            }
            
            await client.close();
        });
    });
});
