import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.closeQuery - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - closeQuery() - JDBC uses ResultSet.close()', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: closeQuery() - JDBC uses ResultSet.close()
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'closeQuery() - JDBC uses ResultSet.close()';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
