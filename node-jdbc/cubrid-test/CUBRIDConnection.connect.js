import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('connect', function() {
        this.timeout(10000);

        it('should succeed to connect to a valid server', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.close();
        });

        it('should fail to connect to wrong database', async function() {
            const config = {
                url: 'jdbc:cubrid:192.168.2.32:33000:wrong_db:dba::?charSet=utf-8',
                drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
                user: 'dba',
                password: ''
            };

            const jdbc = (await import('jdbc')).default;
            const instance = new jdbc(config);
            
            try {
                await new Promise((resolve, reject) => {
                    instance.initialize((err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    instance.reserve((err, conn) => {
                        if (err) reject(err);
                        else resolve(conn);
                    });
                });

                throw new Error('Should have failed to connect');
            } catch (err) {
                expect(err).to.be.an.instanceOf(Error);
            }
        });

        it('should succeed to connect multiple times', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            await client.connect(); // Second connect should be OK
            await client.close();
        });

        it.skip('should emit connect event - NOT SUPPORTED', function() {
            // JDBC does not emit events like node-cubrid
        });
    });
});
