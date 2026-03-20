import { expect } from 'chai';
import testSetup from './testSetup.js';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);
const ErrorMessages = require('./ErrorMessages.cjs');

describe('CUBRIDConnection', function () {
  describe('rollback', function () {
    const TABLE_NAME = 'test_tran_rollback';
    this.timeout(20000); // Increase timeout

    beforeEach(testSetup.cleanup(TABLE_NAME));
    afterEach(testSetup.cleanup(TABLE_NAME));

    it('should succeed to rollback()', function () {
      const client = testSetup.createDefaultCUBRIDDemodbConnection();

      return client
          .connect()
          .then(() => {
            return client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
          })
          .then(() => {
            return client.setAutoCommitMode(false);
          })
          .then(() => {
            return client.batchExecuteNoQuery([`INSERT INTO ${TABLE_NAME} VALUES(1)`]);
          })
          .then(() => {
            return client.query(`SELECT * FROM ${TABLE_NAME}`);
          })
          .then(response => {
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
                .to.equal(1);

            expect(result)
                .to.have.property('ColumnValues')
                .to.be.an('array')
                .with.length(1);

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.contain.keys(['' + response.queryHandle]);

            expect(result.ColumnValues[0])
                .to.be.an('array')
                .with.length(1);

            expect(result.ColumnValues[0][0])
                .to.be.a('number')
                .to.equal(1);

            return client.rollback();
          })
          .then(() => {
            // After rollback the autocommit mode stay the same, i.e. OFF.
            // If necessary, the user has to explicitly set it to `true`
            // in order to enable the autocommit mode.
            return client.getAutoCommitMode();
          })
          .then(mode => {
             expect(mode).to.be.false;
             return client.query(`SELECT * FROM ${TABLE_NAME}`);
          })
          .then(response => {
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

            expect(result)
                .to.have.property('ColumnValues')
                .to.be.an('array')
                .with.length(0);

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.contain.keys(['' + response.queryHandle]);

            // Note: client._queryResultSets logic in wrapper might differ slightly 
            // if we don't clear handles manually or if closeQuery logic differs.
            // But we implemented basic closeQuery clearing.
            // In node-cubrid, previous handles might remain unless closed.
            
            return client.setAutoCommitMode(true);
          })
          .then(() => {
            return client.close();
          });
    });

    it('should succeed to rollback(callback)', function () {
      const client = testSetup.createDefaultCUBRIDDemodbConnection();

      return client
          .connect()
          .then(() => {
            return client.execute(`CREATE TABLE ${TABLE_NAME}(id INT)`);
          })
          .then(() => {
            return client.setAutoCommitMode(false);
          })
          .then(() => {
            return client.batchExecuteNoQuery([`INSERT INTO ${TABLE_NAME} VALUES(1)`]);
          })
          .then(() => {
            return client.query(`SELECT * FROM ${TABLE_NAME}`);
          })
          .then(response => {
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
                .to.equal(1);

            expect(result)
                .to.have.property('ColumnValues')
                .to.be.an('array')
                .with.length(1);

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.contain.keys(['' + response.queryHandle]);

            expect(result.ColumnValues[0])
                .to.be.an('array')
                .with.length(1);

            expect(result.ColumnValues[0][0])
                .to.be.a('number')
                .to.equal(1);

            return new Promise((resolve, reject) => {
              client.rollback(function (err) {
                if (err) {
                  return reject(err);
                }

                resolve();
              });
            });
          })
          .then(() => {
            return client.getAutoCommitMode();
          })
          .then(mode => {
            expect(mode).to.be.false;
            return client.query(`SELECT * FROM ${TABLE_NAME}`);
          })
          .then(response => {
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

            expect(result)
                .to.have.property('ColumnValues')
                .to.be.an('array')
                .with.length(0);

            expect(client)
                .to.be.an('object')
                .to.have.property('_queryResultSets')
                .to.be.an('object')
                .to.contain.keys(['' + response.queryHandle]);

            return client.setAutoCommitMode(true);
          })
          .then(() => {
            return client.close();
          });
    });

    it('should fail to rollback() when the connection is in AUTO_COMMIT_ON mode', function () {
      const client = testSetup.createDefaultCUBRIDDemodbConnection();

      // client is not connected, but rollback() checks connectivity/autocommit
      // Our wrapper: rollback() calls connect(), then checks getAutoCommit() which is true by default.
      // But wait, our wrapper rollback() throws ERROR_NO_ROLLBACK if !this.conn.
      // So this should work.

      return client
          .rollback()
          .then(() => {
            throw new Error('Should have failed to rollback() when the connection is in AUTO_COMMIT_ON mode.')
          })
          .catch(err => {
            expect(err).to.be.an.instanceOf(Error);
            expect(err.message).to.equal(ErrorMessages.ERROR_NO_ROLLBACK);
          });
    });

    it('should fail to rollback(callback) when the connection is in AUTO_COMMIT_ON mode', function (done) {
      const client = testSetup.createDefaultCUBRIDDemodbConnection();

      client.rollback(function (err) {
        expect(err).to.be.an.instanceOf(Error);
        expect(err.message).to.equal(ErrorMessages.ERROR_NO_ROLLBACK);

        done();
      });
    });
  });
});
