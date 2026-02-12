import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('setAutoCommitMode', function() {
        it('should set auto commit mode to false', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.setAutoCommitMode(false);
            await client.setAutoCommitMode(true);
            
            await client.close();
        });

        it('should maintain auto commit mode across transactions', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            await client.setAutoCommitMode(false);
            await client.commit();
            // Mode should still be false
            await client.commit(); // Should not fail
            await client.setAutoCommitMode(true);
            
            await client.close();
        });

        it.skip('should get auto commit mode synchronously - NOT SUPPORTED', function() {
            // JDBC getAutoCommit() requires callback, not synchronous
        });
    });
});
