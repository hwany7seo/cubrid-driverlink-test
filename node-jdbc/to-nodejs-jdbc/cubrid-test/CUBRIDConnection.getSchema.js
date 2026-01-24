import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('getSchema', function() {
        this.timeout(20000);

        it('should get schema information', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Should return promise if no callback
            const schema = await client.getSchema();
            expect(schema).to.be.an('object');
            expect(schema).to.have.property('tables');
            // We can't guarantee tables content without setup, but function should run
            
            await client.close();
        });

        it('should get schema with callback', function(done) {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            client.connect().then(() => {
                client.getSchema(function(err, schema) {
                    if (err) return done(err);
                    expect(schema).to.be.an('object');
                    client.close().then(() => done());
                });
            });
        });
    });
});
