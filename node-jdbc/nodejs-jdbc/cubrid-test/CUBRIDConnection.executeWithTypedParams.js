import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('executeWithTypedParams', function() {
        const TABLE_NAME = 'tbl_test_typed';
        this.timeout(10000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should simulate executeWithTypedParams using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, name VARCHAR(100))`);
            
            // Using wrapper prepareStatement
            const ps = await client.prepareStatement(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`);
            
            // Setting parameters by type
            await ps.setInt(1, 100);
            await ps.setString(2, 'Test Typed');
            
            await ps.executeUpdate();
            await ps.close();
            
            // Verify
            const resultObj = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            const result = resultObj.result.rows;
            expect(result[0].ID).to.equal(100);
            expect(result[0].NAME).to.equal('Test Typed');
            
            await client.close();
        });
    });
});
