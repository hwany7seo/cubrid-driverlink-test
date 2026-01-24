import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('fetch', function() {
        const TABLE_NAME = 'tbl_test_fetch_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should fetch all rows', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2), (3)`);
            
            const res = await client.fetch(`SELECT * FROM ${TABLE_NAME}`);
            expect(res.result.RowsCount).to.equal(3);
            expect(res.result.rows.length).to.equal(3);
            
            await client.close();
        });

        it('should fetch with params', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2), (3)`);
            
            const res = await client.fetch(`SELECT * FROM ${TABLE_NAME} WHERE id > ?`, [1]);
            expect(res.result.RowsCount).to.equal(2);
            
            await client.close();
        });
    });
});
