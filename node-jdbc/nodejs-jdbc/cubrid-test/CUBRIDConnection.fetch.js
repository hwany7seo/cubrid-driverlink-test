import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('fetch', function() {
        const TABLE_NAME = 'tbl_test_fetch';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should fetch all rows', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(2)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(3)`);
            
            // In node-cubrid fetch implies partial retrieval or cursor usage
            // In wrapper, query fetches all via toObjArray
            const resultObj = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            
            expect(resultObj.result.rows.length).to.equal(3);
            
            await client.close();
        });
    });
});
