import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('commit', function() {
        const TABLE_NAME = 'tbl_test_commit';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should commit successfully', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Transaction Start
            await client.beginTransaction();
            
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            await client.commit();
            
            // Verify
            const resultObj = await client.query(`SELECT count(*) as CNT FROM ${TABLE_NAME}`);
            expect(Number(resultObj.result.rows[0].CNT)).to.equal(1);
            
            await client.close();
        });
    });
});
