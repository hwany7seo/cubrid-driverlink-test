import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('rollback', function() {
        const TABLE_NAME = 'test_rollback';

        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should succeed to rollback transaction', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            await client.execute(`DROP TABLE IF EXISTS ${TABLE_NAME}`);
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            await client.setAutoCommitMode(false);
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            await client.rollback();
            await client.setAutoCommitMode(true);
            
            // Verify rollback worked
            const result = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            const data = await new Promise((resolve) => {
                result.resultSet.toObject((err, obj) => {
                    result.resultSet.close(() => {
                        result.statement.close(() => {
                            resolve(obj);
                        });
                    });
                });
            });
            
            expect(data.rows).to.have.length(0);
            
            await client.close();
        });

        it('should fail to rollback when autocommit is ON', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            
            try {
                await client.rollback();
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.be.an.instanceOf(Error);
            }
            
            await client.close();
        });
    });
});
