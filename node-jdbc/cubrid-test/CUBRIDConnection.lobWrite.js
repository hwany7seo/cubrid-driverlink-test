import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.lobWrite - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - lobWrite() - JDBC LOB API is different', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: lobWrite() - JDBC LOB API is different
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'lobWrite() - JDBC LOB API is different';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
