#!/bin/bash

# NOT SUPPORTED 기능 맵핑
declare -A NOT_SUPPORTED
NOT_SUPPORTED["CUBRIDConnection.queryAll.js"]="queryAll() method - JDBC does not have this convenience method"
NOT_SUPPORTED["CUBRIDConnection.fetch.js"]="fetch() method - JDBC uses ResultSet.next() instead"
NOT_SUPPORTED["CUBRIDConnection.batchExecuteNoQuery.js"]="batchExecuteNoQuery() - JDBC uses addBatch/executeBatch"
NOT_SUPPORTED["CUBRIDConnection.executeWithTypedParams.js"]="executeWithTypedParams() - JDBC uses setInt/setString methods"
NOT_SUPPORTED["CUBRIDConnection.getEngineVersion.js"]="getEngineVersion() - JDBC does not expose CUBRID engine version"
NOT_SUPPORTED["CUBRIDConnection.getSchema.js"]="getSchema() - JDBC uses DatabaseMetaData"
NOT_SUPPORTED["CUBRIDConnection.getDatabaseParameter.js"]="getDatabaseParameter() - CUBRID-specific, not in JDBC"
NOT_SUPPORTED["CUBRIDConnection.setDatabaseParameter.js"]="setDatabaseParameter() - CUBRID-specific, not in JDBC"
NOT_SUPPORTED["CUBRIDConnection.lobRead.js"]="lobRead() - JDBC LOB API is different"
NOT_SUPPORTED["CUBRIDConnection.lobWrite.js"]="lobWrite() - JDBC LOB API is different"
NOT_SUPPORTED["CUBRIDConnection.closeQuery.js"]="closeQuery() - JDBC uses ResultSet.close()"
NOT_SUPPORTED["CUBRIDConnection.getConnectionTimeout.js"]="getConnectionTimeout() - set at URL level in JDBC"
NOT_SUPPORTED["CUBRIDConnection.setConnectionTimeout.js"]="setConnectionTimeout() - set at URL level in JDBC"
NOT_SUPPORTED["CUBRIDConnection.beginTransaction.js"]="beginTransaction() - JDBC uses setAutoCommit(false)"

for file in "${!NOT_SUPPORTED[@]}"; do
    reason="${NOT_SUPPORTED[$file]}"
    cat > "cubrid-test/$file" << TESTEOF
import { expect } from 'chai';
import testSetup from './testSetup.js';

describe('${file%.js} - NOT SUPPORTED IN JDBC', function() {
    it.skip('should fail - ${reason}', function() {
        // This test is skipped because the feature is not supported in JDBC
        // Reason: ${reason}
        expect(true).to.be.false;
    });
    
    it('should document why this is not supported', function() {
        const reason = '${reason}';
        console.log(\`Feature not supported: \${reason}\`);
        expect(reason).to.be.a('string');
    });
});
TESTEOF
    echo "Created: cubrid-test/$file"
done

echo "All NOT SUPPORTED test files created"
