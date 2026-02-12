import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.getDatabaseParameter - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - getDatabaseParameter() - CUBRID-specific, not in JDBC', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: getDatabaseParameter() - CUBRID-specific, not in JDBC
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'getDatabaseParameter() - CUBRID-specific, not in JDBC';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
