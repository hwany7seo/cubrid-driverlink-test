import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.getEngineVersion - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - getEngineVersion() - JDBC does not expose CUBRID engine version', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: getEngineVersion() - JDBC does not expose CUBRID engine version
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'getEngineVersion() - JDBC does not expose CUBRID engine version';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
