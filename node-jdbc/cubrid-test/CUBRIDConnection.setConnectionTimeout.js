import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.setConnectionTimeout - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - setConnectionTimeout() - set at URL level in JDBC', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: setConnectionTimeout() - set at URL level in JDBC
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'setConnectionTimeout() - set at URL level in JDBC';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
