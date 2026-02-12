import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.executeWithTypedParams - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - executeWithTypedParams() - JDBC uses setInt/setString methods', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: executeWithTypedParams() - JDBC uses setInt/setString methods
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'executeWithTypedParams() - JDBC uses setInt/setString methods';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
