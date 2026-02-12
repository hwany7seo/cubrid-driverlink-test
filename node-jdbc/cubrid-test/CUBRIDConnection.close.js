import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('close', function() {
        it.skip('should emit disconnect event on close - NOT SUPPORTED', function() {
            // JDBC does not emit events like node-cubrid
        });

        it('should succeed to close when connection is not established', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.close();
        });

        it('should succeed to close established connection', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.close();
        });
    });
});
