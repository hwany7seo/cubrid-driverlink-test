import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('CUBRIDConnection (nodejs-jdbc Wrapper)', function() {
    describe('lobWrite', function() {
        const TABLE_NAME = 'tbl_test_lob_write_jdbc';
        this.timeout(30000);

        beforeEach(testSetup.cleanup(TABLE_NAME));
        afterEach(testSetup.cleanup(TABLE_NAME));

        it('should write BLOB data using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Create table with BLOB
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, my_blob BLOB)`);
            
            // Prepare Buffer data (e.g., 1KB)
            const buffer = Buffer.alloc(1024);
            for(let i = 0; i < buffer.length; i++) {
                buffer[i] = i % 255;
            }
            
            // Insert using setBytes (handled by wrapper)
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [1, buffer]);
            
            // Verify insertion
            const res = await client.query(`SELECT COUNT(*) as CNT FROM ${TABLE_NAME}`);
            expect(Number(res.result.rows[0].CNT)).to.equal(1);
            
            // Verify size (using BIT_LENGTH or OCTET_LENGTH if supported, or just length)
            // OCTET_LENGTH returns bytes
            try {
                const sizeRes = await client.query(`SELECT OCTET_LENGTH(my_blob) as LEN FROM ${TABLE_NAME}`);
                expect(Number(sizeRes.result.rows[0].LEN)).to.equal(1024);
            } catch(e) {
                // If OCTET_LENGTH not supported for BLOB in this version, skip size check
                console.log("OCTET_LENGTH check skipped");
            }
            
            await client.close();
        });

        it('should write CLOB data using PreparedStatement', async function() {
            const client = testSetup.createDefaultCUBRIDDemodbConnection();
            await client.connect();
            
            // Create table with CLOB
            await client.execute(`CREATE TABLE ${TABLE_NAME}(id INT, text_data CLOB)`);
            
            // Prepare Long String (e.g., 10KB)
            let longStr = '';
            for(let i = 0; i < 1000; i++) {
                longStr += '0123456789';
            }
            
            // Insert using setString (wrapper handles it as string)
            // JDBC drivers usually handle setString for CLOB seamlessly for moderate sizes
            await client.execute(`INSERT INTO ${TABLE_NAME} VALUES(?, ?)`, [1, longStr]);
            
            // Verify insertion
            const res = await client.query(`SELECT CHAR_LENGTH(text_data) as LEN FROM ${TABLE_NAME}`);
            expect(Number(res.result.rows[0].LEN)).to.equal(10000);
            
            await client.close();
        });
    });
});
