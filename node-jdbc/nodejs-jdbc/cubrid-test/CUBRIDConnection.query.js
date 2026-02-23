import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('query', function() {
        const TABLE_NAME = 'tbl_test_query';
        this.timeout(10000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute simple SELECT query', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Should return result set
            const resultObj = await client.query('SELECT 1 as num');
            const result = resultObj.result;
            expect(result.rows).to.be.an('array');
            // Check first column value regardless of casing
            const firstRow = result.rows[0];
            const firstCol = Object.keys(firstRow)[0];
            expect(firstRow[firstCol]).to.equal(1);
            
            await client.close();
        });

        it('should execute query on SHOW TABLES', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const resultObj = await client.query('SHOW TABLES');
            const result = resultObj.result;
            expect(result.rows).to.be.an('array');
            
            // Ensure at least one table exists (demodb default)
            expect(result.rows.length).to.be.above(0);
            
            await client.close();
        });

        it('should handle parameter binding using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Create table
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, name VARCHAR(100))`);
            
            // Insert data
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1, 'Alice')`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(2, 'Bob')`);
            
            // Query with parameter
            const ps = await client.prepareStatement(`SELECT * FROM ${TABLE_NAME} WHERE id = ?`);
            await ps.setInt(1, 1);
            
            const rs = await ps.executeQuery();
            const resultObj = await rs.toObjArrayAsync(); // Use async wrapper from testSetup
            const result = resultObj.result.rows;
            
            expect(result).to.be.an('array');
            expect(result.length).to.equal(1);
            expect(result[0].NAME).to.equal('Alice');
            
            await rs.closeAsync(); // Use async wrapper
            await ps.close(); // Wrapper handles this
            
            await client.close();
        });
    });
});
