import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.fetch - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - fetch() method - JDBC uses ResultSet.next() instead', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: fetch() method - JDBC uses ResultSet.next() instead
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'fetch() method - JDBC uses ResultSet.next() instead';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
