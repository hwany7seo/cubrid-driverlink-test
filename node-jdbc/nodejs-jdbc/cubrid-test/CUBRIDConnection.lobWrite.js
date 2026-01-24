import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('lobWrite', function() {
        it('should skip LOB write tests as nodejs-jdbc handles LOBs differently', function() {
            this.skip();
        });
    });
});
