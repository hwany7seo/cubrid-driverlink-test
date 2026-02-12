import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.beginTransaction - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - beginTransaction() - JDBC uses setAutoCommit(false)', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: beginTransaction() - JDBC uses setAutoCommit(false)
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'beginTransaction() - JDBC uses setAutoCommit(false)';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
