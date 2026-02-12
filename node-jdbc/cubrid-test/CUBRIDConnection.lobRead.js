import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection.lobRead - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - lobRead() - JDBC LOB API is different', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: lobRead() - JDBC LOB API is different
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = 'lobRead() - JDBC LOB API is different';
        console.log(`Feature not supported: ${reason}`);
        expect(reason).to.be.a('string');
    });
});
