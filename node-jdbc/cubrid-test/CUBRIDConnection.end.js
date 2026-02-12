import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('end', function() {
        it('should end connection (alias for close)', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.close(); // end() is same as close() in JDBC
        });

        it('should succeed to end when not connected', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.close();
        });
    });
});
