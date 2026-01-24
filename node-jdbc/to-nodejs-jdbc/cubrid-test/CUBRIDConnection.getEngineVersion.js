import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('getEngineVersion', function() {
        this.timeout(10000);

        it('should return CUBRID Engine Version via DatabaseMetaData', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Calls conn.getMetaData().getDatabaseProductVersion()
            const version = await client.getEngineVersion();
            
            expect(version).to.be.a('string');
            expect(version.length).to.be.above(0);
            
            // Should contain CUBRID
            expect(version.toUpperCase()).to.contain('CUBRID');
            
            await client.close();
        });
    });
});
