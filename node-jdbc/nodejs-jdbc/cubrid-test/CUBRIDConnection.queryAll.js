import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('queryAll', function() {
        const TABLE_NAME = 'tbl_test_query_all';
        this.timeout(10000);
        
        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should behave like query() for SELECT', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2)`);
            
            // Using queryAll alias
            const result = await client.queryAll(`SELECT * FROM ${TABLE_NAME}`);
            
            expect(result.result).to.be.an('array');
            expect(result.result.length).to.equal(2);
            
            await client.close();
        });
    });
});
