import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.batchExecuteNoQuery - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - batchExecuteNoQuery() - JDBC uses addBatch/executeBatch', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: batchExecuteNoQuery() - JDBC uses addBatch/executeBatch
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'batchExecuteNoQuery() - JDBC uses addBatch/executeBatch';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
