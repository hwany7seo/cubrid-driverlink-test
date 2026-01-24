import { expect } from 'chai';
import testSetup from './testSetup.js';
import { CUBRIDAsyncWrapper, ErrorMessages } from './testSetup.js';

describe('CUBRIDConnection', function() {
    this.timeout(20000);

    describe('connect', function() {
        describe('to a non-existing database', function() {
            it('should fail to connect to a non-existing database', async function() {
                const badConfig = JSON.parse(JSON.stringify(testSetup.defaultConfig));
                badConfig.url = 'jdbc:cubrid:localhost:33000:non_existing_db:public::?charSet=utf-8';
                
                const client = new CUBRIDAsyncWrapper(badConfig);
                try {
                    await client.connect();
                    throw new Error('Should have failed');
                } catch (err) {
                    expect(err).to.exist;
                    // JDBC error message might differ, check for common keywords
                    // "Unknown database" or similar
                    expect(err.message).to.match(/unknown database|failed to connect/i);
                }
            });
        });

        describe('to a wrong port', function() {
            it('should fail to connect to an incorrect port', async function() {
                const badConfig = JSON.parse(JSON.stringify(testSetup.defaultConfig));
                // Assuming 80 is not a CUBRID port
                badConfig.url = badConfig.url.replace(':33000:', ':80:');
                
                const client = new CUBRIDAsyncWrapper(badConfig);
                try {
                    await client.connect();
                    throw new Error('Should have failed');
                } catch (err) {
                    expect(err).to.exist;
                    // Connection refused or similar, or generic wrapper error
                    expect(err.message).to.match(/connect.*refused|connection.*refused|error.*static.*method/i);
                }
            });
        });

        describe('when a username is invalid', function() {
            it('should fail to connect when a username is invalid', async function() {
                const badConfig = JSON.parse(JSON.stringify(testSetup.defaultConfig));
                badConfig.properties.user = 'unknown_user';
                
                const client = new CUBRIDAsyncWrapper(badConfig);
                try {
                    await client.connect();
                    throw new Error('Should have failed');
                } catch (err) {
                    expect(err).to.exist;
                    // Check for authentication failure or invalid user
                    expect(err.message).to.match(/invalid.*user|authentication.*failed/i);
                }
            });
        });

        describe('to a valid server', function() {
            it('should succeed to connect to a valid server', async function() {
                const client = testSetup.createDefaultCUBRIDDemodbConnection();
                await client.connect();
                expect(client.getRawConnection()).to.exist;
                await client.close();
            });

            it('should succeed to call connect() multiple times (idempotent)', async function() {
                const client = testSetup.createDefaultCUBRIDDemodbConnection();
                await client.connect();
                // Second call should just return or be handled gracefully
                await client.connect();
                expect(client.getRawConnection()).to.exist;
                await client.close();
            });
        });
    });
});
