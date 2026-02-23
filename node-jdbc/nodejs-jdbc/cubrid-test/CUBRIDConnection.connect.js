import { expect } from 'chai';
import testSetup from './testSetup.js';
import { CUBRIDAsyncWrapper } from './testSetup.js';

describe('CUBRIDConnection', function() {
    this.timeout(10000);

    describe('connect', function() {
        it('should succeed to connect to a valid server', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            // JDBC wrapper sets conn object on success
            const rawConn = client.getRawConnection();
            expect(rawConn).to.exist;
            await client.close();
        });

        it('should fail to connect to wrong database', async function() {
            // Modify config for failure
            const badConfig = JSON.parse(JSON.stringify(testSetup.defaultConfig));
            badConfig.url = 'jdbc:cubrid:localhost:33000:non_existing_db:public::?charSet=utf-8';
            
            const client = new CUBRIDAsyncWrapper(badConfig);
            try {
                await client.connect();
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.exist;
            }
        });
    });
});
