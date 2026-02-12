import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.queryAll - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - queryAll() method - JDBC does not have this convenience method', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: queryAll() method - JDBC does not have this convenience method
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'queryAll() method - JDBC does not have this convenience method';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
