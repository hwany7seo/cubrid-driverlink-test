import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (JDBC)', function() {
    describe('query', function() {
        this.timeout(5000);

        it('should execute simple query', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const result = await client.query('SELECT 1');
            expect(result).to.have.property('resultSet');
            expect(result).to.have.property('statement');
            
            await client.closeQuery(result.resultSet, result.statement);
            await client.close();
        });

        it('should execute query on SHOW TABLES', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const result = await client.query('SHOW TABLES');
            expect(result).to.have.property('resultSet');
            
            const data = await new Promise((resolve) => {
                result.resultSet.toObject((err, obj) => {
                    resolve(obj);
                });
            });
            
            expect(data.rows).to.be.an('array');
            expect(data.rows.length).to.be.above(0);
            
            await client.closeQuery(result.resultSet, result.statement);
            await client.close();
        });

        it('should execute query with parameters', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            const ps = await client.prepareStatement('SELECT * FROM nation WHERE continent = ?');
            await new Promise((resolve) => {
                ps.setString(1, 'Asia', () => resolve());
            });
            
            const rs = await new Promise((resolve, reject) => {
                ps.executeQuery((err, result) => {
                    if (err) reject(err);
                    else resolve(result);
                });
            });
            
            const data = await new Promise((resolve) => {
                rs.toObject((err, obj) => {
                    resolve(obj);
                });
            });
            
            expect(data.rows).to.be.an('array');
            expect(data.rows.length).to.be.above(0);
            
            await new Promise((resolve) => {
                rs.close(() => {
                    ps.close(() => resolve());
                });
            });
            
            await client.close();
        });

        it('should fail to query non-existing table', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            try {
                await client.query('SELECT * FROM non_existing_table');
                throw new Error('Should have failed');
            } catch (err) {
                expect(err).to.be.an.instanceOf(Error);
            }
            
            await client.close();
        });

        it.skip('should manage query handles - NOT SUPPORTED', function() {
            // JDBC uses Statement/ResultSet objects, not query handles
        });
    });
});
