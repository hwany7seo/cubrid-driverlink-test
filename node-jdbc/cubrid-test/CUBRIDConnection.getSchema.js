import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.getSchema - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - getSchema() - JDBC uses DatabaseMetaData', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: getSchema() - JDBC uses DatabaseMetaData
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'getSchema() - JDBC uses DatabaseMetaData';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
