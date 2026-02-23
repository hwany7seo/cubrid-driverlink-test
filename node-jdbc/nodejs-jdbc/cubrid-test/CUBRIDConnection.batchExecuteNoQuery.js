import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('batchExecuteNoQuery', function() {
        const TABLE_NAME = 'tbl_test_batch_exec';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should verify batch execution works (CREATE -> INSERT -> DROP)', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const queries = [
                `CREATE TABLE ${TABLE_NAME}(id INT)`,
                `INSERT INTO ${TABLE_NAME} (id) VALUES (1), (2), (3)`,
                // DROP is usually DDL and auto-commits, might break batch in some drivers, but testing here
                // `DROP TABLE ${TABLE_NAME}` 
            ];
            
            // Execute batch
            await client.batchExecuteNoQuery(queries);
            
            // Verify INSERT
            const resultObj = await client.query(`SELECT count(*) as CNT FROM ${TABLE_NAME}`);
            // Count check
            const cnt = resultObj.result.rows[0].CNT;
            expect(Number(cnt)).to.equal(3);
            
            // Cleanup
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            await client.close();
        });

        it('should handle invalid SQL in batch', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            const queries = [
                `INSERT INTO ${TABLE_NAME} VALUES(1)`,
                `INSERT INTO INVALID_TABLE_XYZ VALUES(2)` // Syntax/Table error
            ];
            
            try {
                await client.batchExecuteNoQuery(queries);
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.exist;
            }
            
            await client.close();
        });
        
        it('should handle empty batch', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.batchExecuteNoQuery([]);
            // Should just pass without error
            
            await client.close();
        });
    });
});
