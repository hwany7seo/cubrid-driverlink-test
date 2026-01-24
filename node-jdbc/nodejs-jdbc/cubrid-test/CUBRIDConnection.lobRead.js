import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('lobRead', function() {
        it('should skip LOB read tests as nodejs-jdbc handles LOBs differently', function() {
            this.skip();
        });
    });
});
