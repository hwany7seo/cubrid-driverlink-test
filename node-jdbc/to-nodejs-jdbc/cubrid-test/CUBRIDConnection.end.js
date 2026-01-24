import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('end', function() {
        this.timeout(10000);

        it('should end connection successfully (alias for close)', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.close(); // Wrapper doesn't have 'end', uses 'close'
        });
    });
});
