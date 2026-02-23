import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('setAutoCommitMode', function() {
        const TABLE_NAME = 'tbl_test_autocommit';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should toggle auto-commit mode', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Set true
            await client.setAutoCommit(true);
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Set false
            await client.setAutoCommit(false);
            
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            await client.rollback();
            
            const resultObj = await client.query(`SELECT count(*) as CNT FROM ${TABLE_NAME}`);
            expect(Number(resultObj.result.rows[0].CNT)).to.equal(0);
            
            await client.close();
        });
    });
});
