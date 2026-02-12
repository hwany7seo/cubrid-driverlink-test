import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('execute', function() {
        const TABLE_NAME = 'tbl_test';

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should execute simple DDL', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            
            await client.close();
        });

        it('should execute INSERT with parameters', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, name VARCHAR(100))`);
            
            const ps = await client.prepareStatement(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`);
            await new Promise((resolve) => {
                ps.setInt(1, 1, () => {
                    ps.setString(2, 'test', () => {
                        ps.executeUpdate(() => {
                            ps.close(() => resolve());
                        });
                    });
                });
            });
            
            await client.close();
        });

        it('should execute multiple statements', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1), (2), (3)`);
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            
            await client.close();
        });
    });
});
