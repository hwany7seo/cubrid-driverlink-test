import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('closeQuery', function() {
        const TABLE_NAME = 'tbl_test_close_query';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should close query ResultSet successfully', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            
            const ps = await client.prepareStatement(`SELECT * FROM ${TABLE_NAME}`);
            const rs = await ps.executeQuery();
            
            // Check results
            const resultObj = await rs.toObjArrayAsync();
            expect(resultObj.result.rows.length).to.equal(1);
            
            // Close ResultSet explicitly
            await rs.closeAsync();
            
            // Should not be able to operate on closed RS - hard to verify in JDBC without causing issues
            // Just verifying closeAsync completes without error
            
            await ps.close();
            await client.close();
        });
    });
});
