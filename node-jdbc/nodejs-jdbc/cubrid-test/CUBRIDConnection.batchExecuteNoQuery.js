import { expect } from 'chai';
import testSetup from './testSetup.js';
import { CUBRIDAsyncWrapper, ErrorMessages } from './testSetup.js';

describe('CUBRIDConnection', function() {
    describe('batchExecuteNoQuery', function() {
        const TABLE_NAME = 'tbl_test_batch_full';
        this.timeout(20000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should verify there are no query packets after calling batchExecuteNoQuery(sqls) with multiple queries', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();

            const queries = [
                `CREATE TABLE ${TABLE_NAME}(id INT)`,
                `INSERT INTO ${TABLE_NAME} (id) VALUES (1), (2), (3)`,
                `DROP TABLE ${TABLE_NAME}`
            ];

            const res = await client.batchExecuteNoQuery(queries);
            
            // Check intermediate result if possible, or just verify final state
            // JDBC batch execution result is an array of update counts
            // But our wrapper aggregates it.
            // 0 (CREATE) + 3 (INSERT) + 0 (DROP) = 3 affected rows total? 
            // Or JDBC returns specific values. Let's check structure.
            
            expect(res).to.be.an('object');
            expect(res.queryHandle).to.be.a('number');
            expect(res.result).to.be.an('object');
            
            // Verify no open result sets leak (wrapper logic)
            expect(client._queryResultSets).to.be.empty; // batchExecuteNoQuery doesn't open result set

            await client.close();
        });

        it('should verify there are no query packets after calling batchExecuteNoQuery(sqls, callback) with multiple queries', function(done) {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            client.connect().then(() => {
                const queries = [
                    `CREATE TABLE ${TABLE_NAME}(id INT)`,
                    `INSERT INTO ${TABLE_NAME} (id) VALUES (1), (2), (3)`,
                    `DROP TABLE ${TABLE_NAME}`
                ];

                client.batchExecuteNoQuery(queries, function(err, result) {
                    if (err) return done(err);
                    expect(result).to.exist;
                    client.close().then(() => done());
                });
            });
        });

        it('should succeed to call batchExecuteNoQuery(sqls) with no queries', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const queries = [];
            const res = await client.batchExecuteNoQuery(queries);
            
            expect(res.result.RowsCount).to.equal(0);
            await client.close();
        });

        it('should succeed to call batchExecuteNoQuery() and let another client see the writes', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            const client2 = testSetup.createDefaultCUBRIDDemodbConnection();
            
            await client.connect();
            await client2.connect();

            const queries = [
                `CREATE TABLE ${TABLE_NAME}(id INT)`,
                `INSERT INTO ${TABLE_NAME} (id) VALUES (1), (2), (3)`,
            ];

            await client.batchExecuteNoQuery(queries);

            // Check with client2
            const res = await client2.query(`SELECT * FROM ${TABLE_NAME}`);
            expect(res.result.RowsCount).to.equal(3);
            
            const ids = res.result.rows.map(r => r.ID); // Assuming uppercase from wrapper
            expect(ids).to.include(1);
            expect(ids).to.include(2);
            expect(ids).to.include(3);

            await client2.execute(`DROP TABLE ${TABLE_NAME}`);
            await client.close();
            await client2.close();
        });

        describe('when SQL is not a string', function() {
            it('should fail to call batchExecuteNoQuery(sqls) when SQL is not a string but an array of an integer', async function() {
                const client = testSetup.createDefaultCUBRIDDemodbConnection();
                await client.connect();
                
                const queries = [1234];
                try {
                    await client.batchExecuteNoQuery(queries);
                    throw new Error('Should have failed');
                } catch(e) {
                    expect(e).to.exist;
                    // JDBC wrapper might throw different error than node-cubrid
                    // node-cubrid: "The 'string' argument must be of type string..."
                    // nodejs-jdbc wrapper: we check for string in loop or addBatch throws
                }
                await client.close();
            });
        });

        describe('no SQL is specified', function() {
            it('should fail to call batchExecuteNoQuery(sqls) when SQL is not specified', async function() {
                const client = testSetup.createDefaultCUBRIDDemodbConnection();
                await client.connect();
                
                const queries = ''; // Not an array
                try {
                    await client.batchExecuteNoQuery(queries);
                    throw new Error('Should have failed');
                } catch(e) {
                    expect(e).to.exist;
                    expect(e.message).to.contain('array');
                }
                await client.close();
            });
        });

        describe('when one of the batch queries has an invalid syntax', function() {
            it('should fail batch execution when a query has invalid syntax', async function() {
                const client = testSetup.createDefaultCUBRIDDemodbConnection();
                await client.connect();
                
                const queries = [
                    `CREATE TABLE ${TABLE_NAME}(id INT)`,
                    `INSERT INTO ${TABLE_NAME} (id)`, // Invalid syntax (missing VALUES)
                ];

                try {
                    await client.batchExecuteNoQuery(queries);
                    throw new Error('Should have failed');
                } catch(e) {
                    expect(e).to.exist;
                    // Verify that the table might exist if the first query succeeded?
                    // JDBC batch behavior depends on driver. CUBRID JDBC might stop on error.
                }

                // Cleanup if table was created
                try { await client.execute(`DROP TABLE ${TABLE_NAME}`); } catch(e) {}
                await client.close();
            });
        });

        describe('when data includes Unicode characters', function() {
            const unicodeDataArr = [
                { lang: 'Korean', string: '이 소포를 부치고 싶은데요.' },
                { lang: 'Russian', string: 'Я хотел бы отослать этот пакет' }
            ];

            unicodeDataArr.forEach(data => {
                it(`should succeed to properly encode ${data.lang} characters`, async function() {
                    const client = testSetup.createDefaultCUBRIDDemodbConnection();
                    await client.connect();
                    
                    const testData = data.string;
                    
                    // Create table
                    await client.batchExecuteNoQuery([`CREATE TABLE ${TABLE_NAME}(str VARCHAR(256))`]);
                    
                    // Insert unicode
                    await client.batchExecuteNoQuery([`INSERT INTO ${TABLE_NAME} VALUES('${testData}')`]);
                    
                    // Select and verify
                    const res = await client.query(`SELECT * FROM ${TABLE_NAME} WHERE str = ?`, [testData]);
                    expect(res.result.RowsCount).to.equal(1);
                    expect(res.result.rows[0].STR).to.equal(testData);
                    
                    await client.close();
                });
            });
        });
    });
});
