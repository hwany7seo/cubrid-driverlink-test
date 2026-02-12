import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('commit', function() {
        it('should fail to commit when connection is offline', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            try {
                await client.commit();
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.be.an.instanceOf(Error);
            }
        });

        it('should succeed to commit transaction', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            await client.setAutoCommitMode(false);
            await client.commit();
            await client.setAutoCommitMode(true);
            await client.close();
        });

        it('should commit after insert', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            await client.setAutoCommitMode(false);
            
            const stmt = await client.createStatement();
            await new Promise((resolve) => {
                stmt.execute('DROP TABLE IF EXISTS test_commit', () => {
                    resolve();
                });
            });
            
            await new Promise((resolve) => {
                stmt.execute('CREATE TABLE test_commit(id INT)', () => {
                    resolve();
                });
            });
            
            await new Promise((resolve) => {
                stmt.execute('INSERT INTO test_commit VALUES(1)', () => {
                    resolve();
                });
            });
            
            await client.commit();
            
            await new Promise((resolve) => {
                stmt.execute('DROP TABLE test_commit', () => {
                    stmt.close(() => resolve());
                });
            });
            
            await client.setAutoCommitMode(true);
            await client.close();
        });
    });
});
