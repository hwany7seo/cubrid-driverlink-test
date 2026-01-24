import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('beginTransaction', function() {
        const TABLE_NAME = 'tbl_test_begin_tx_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should succeed to automatically rollback the changes when the connection has been abruptly disconnected without commit after using beginTransaction()', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
            
            await client.beginTransaction();
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`);
            
            // Abruptly close to verify rollback
            await client.close();
            
            // Reconnect and verify
            // Wrapper's query() auto-connects
            const response = await client.query(`SELECT * FROM ${TABLE_NAME}`);
            
            // Calling `query` above will reestablish the connection
            // which in turn will set auto commit mode back to true.
            expect(await client.getAutoCommitMode()).to.be.true;

            expect(response)
                .to.be.an('object')
                .to.have.property('queryHandle')
                .to.be.a('number')
                .to.be.above(0);

            expect(response)
                .to.have.property('result')
                .to.be.an('object');

            let result = response.result;

            expect(result)
                .to.be.an('object')
                .to.have.property('RowsCount')
                .to.be.a('number')
                .to.equal(0);

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.have.all.keys(['' + response.queryHandle]);
            
            await client.execute(`DROP TABLE ${TABLE_NAME}`);
            await client.close();
            
            // Verify table gone
            const countResponse = await client.query(`SELECT COUNT(*) FROM db_class WHERE class_name = '${TABLE_NAME.toUpperCase()}'`);
            
            expect(countResponse)
                .to.be.an('object')
                .to.have.property('queryHandle')
                .to.be.a('number')
                .to.be.above(0);

            expect(countResponse)
                .to.have.property('result')
                .to.be.an('object');

            let countResult = countResponse.result;

            expect(countResult)
                .to.be.an('object')
                .to.have.property('RowsCount')
                .to.be.a('number')
                .to.equal(1);

            expect(countResult)
                .to.have.property('ColumnValues')
                .to.be.an('array')
                .with.length(1);

            expect(countResult.ColumnValues[0])
                .to.be.an('array')
                .with.length(1);

            // nodejs-jdbc returns count as string '0', handle both
            const countVal = countResult.ColumnValues[0][0];
            expect(Number(countVal)).to.equal(0);

            expect(countResult)
                .to.have.property('ColumnNames')
                .to.be.an('array')
                .with.length(1);

            // testSetup normalizes column names to UPPERCASE
            expect(countResult.ColumnNames[0])
                .to.equal('COUNT(*)');

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.have.all.keys(['' + countResponse.queryHandle]);
            
            await client.close();
        });

        it('should succeed to automatically rollback the changes when the connection has been abruptly disconnected without commit after using beginTransaction(callback)', function(done) {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            
            client.connect()
                .then(() => client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`))
                .then(() => {
                    return new Promise((resolve, reject) => {
                        client.beginTransaction(function(err) {
                            if (err) return reject(err);
                            resolve();
                        });
                    });
                })
                .then(() => client.execute(`INSERT INTO ${TABLE_NAME} VALUES(1)`))
                .then(() => client.close()) // close simulates disconnect/rollback
                .then(() => client.query(`SELECT * FROM ${TABLE_NAME}`))
                .then(async (response) => {
                    expect(await client.getAutoCommitMode()).to.be.true;

                    expect(response)
                        .to.be.an('object')
                        .to.have.property('queryHandle')
                        .to.be.a('number')
                        .to.be.above(0);

                    expect(response)
                        .to.have.property('result')
                        .to.be.an('object');

                    let result = response.result;

                    expect(result)
                        .to.be.an('object')
                        .to.have.property('RowsCount')
                        .to.be.a('number')
                        .to.equal(0);

                    expect(client)
                        .to.be.an('object')
                        .to.have.property('_queryResultSets')
                        .to.be.an('object')
                        .to.have.all.keys(['' + response.queryHandle]);

                    return client.execute(`DROP TABLE ${TABLE_NAME}`);
                })
                .then(() => client.close())
                .then(() => client.query(`SELECT COUNT(*) FROM db_class WHERE class_name = '${TABLE_NAME.toUpperCase()}'`))
                .then(countResponse => {
                    expect(countResponse)
                        .to.be.an('object')
                        .to.have.property('queryHandle')
                        .to.be.a('number')
                        .to.be.above(0);

                    expect(countResponse)
                        .to.have.property('result')
                        .to.be.an('object');

                    let countResult = countResponse.result;

                    expect(countResult)
                        .to.be.an('object')
                        .to.have.property('RowsCount')
                        .to.be.a('number')
                        .to.equal(1);

                    expect(countResult)
                        .to.have.property('ColumnValues')
                        .to.be.an('array')
                        .with.length(1);

                    expect(countResult.ColumnValues[0])
                        .to.be.an('array')
                        .with.length(1);

                    const countVal = countResult.ColumnValues[0][0];
                    expect(Number(countVal)).to.equal(0);

                    expect(countResult)
                        .to.have.property('ColumnNames')
                        .to.be.an('array')
                        .with.length(1);

                    expect(countResult.ColumnNames[0])
                        .to.equal('COUNT(*)');

                    expect(client)
                        .to.be.an('object')
                        .to.have.property('_queryResultSets')
                        .to.be.an('object')
                        .to.have.all.keys(['' + countResponse.queryHandle]);

                    return client.close();
                })
                .then(() => done())
                .catch(done);
        });
    });
});
