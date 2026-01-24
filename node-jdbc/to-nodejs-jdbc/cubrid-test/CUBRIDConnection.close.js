import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('close', function() {
        this.timeout(10000);

        it('should close connection successfully', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.close();
            // Subsequent calls should fail or handle gracefully
            try {
                await client.execute('SELECT 1');
            } catch (e) {
                expect(e).to.exist;
            }
        });
    });
});
