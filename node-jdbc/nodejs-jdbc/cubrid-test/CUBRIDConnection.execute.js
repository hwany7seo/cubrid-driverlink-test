import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('execute', function() {
        const TABLE_NAME = 'tbl_test_execute';
        this.timeout(10000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute simple DDL (CREATE/DROP)', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Execute DDL
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            // Execute another DDL (Check table existence implicitly by drop)
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            
            await client.close();
        });

        it('should execute INSERT with parameters using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, name VARCHAR(100))`);
            
            // Using wrapper prepareStatement
            const ps = await client.prepareStatement(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`);
            
            await ps.setInt(1, 1);
            await ps.setString(2, 'test_user');
            await ps.executeUpdate();
            await ps.close();

            // Verify
            const resultObj = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            const result = resultObj.result.rows;
            expect(result).to.be.an('array');
            expect(result[0].ID).to.equal(1);
            expect(result[0].NAME).to.equal('test_user');
            
            await client.close();
        });

        it('should fail on invalid SQL', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            try {
                await client.execute('SELECT * FROM non_existing_table_xyz');
                throw new Error('Should have failed');
            } catch (err) {
                // Check for JDBC error
                expect(err).to.exist;
            }
            
            await client.close();
        });
    });
});
