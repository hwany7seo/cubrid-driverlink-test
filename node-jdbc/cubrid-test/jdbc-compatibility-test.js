import jdbc from 'jdbc';
import { expect } from 'chai';
import jinst from '../node_modules/jdbc/lib/jinst.js';
import winston from 'winston';

const logger = winston.createLogger({
    level: 'info',
    format: winston.format.simple(),
    transports: [new winston.transports.Console()]
});

if (!jinst.isJvmCreated()) {
    jinst.addOption("-Djava.awt.headless=true");
    jinst.addOption("-Xmx512m");
    jinst.setupClasspath(['./lib/cubrid-jdbc-11.3.0.0047.jar']);
}

const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    user: 'dba',
    password: ''
};

const testResults = {
    passed: [],
    failed: [],
    notSupported: []
};

function logResult(testName, status, message = '') {
    const result = { test: testName, message };
    if (status === 'pass') {
        testResults.passed.push(result);
        console.log(`✓ ${testName}`);
    } else if (status === 'fail') {
        testResults.failed.push(result);
        console.log(`✗ ${testName}: ${message}`);
    } else if (status === 'not-supported') {
        testResults.notSupported.push(result);
        console.log(`⊗ ${testName}: ${message}`);
    }
}

describe('CUBRID JDBC Compatibility Tests', function() {
    let instance;
    let connection;

    this.timeout(60000);

    before(async function() {
        instance = new jdbc(config);
        await new Promise((resolve, reject) => {
            instance.initialize((err) => {
                if (err) reject(err);
                else resolve();
            });
        });
        
        const conn = await new Promise((resolve, reject) => {
            instance.reserve((err, conn) => {
                if (err) reject(err);
                else resolve(conn.conn);
            });
        });
        connection = conn;
    });

    after(async function() {
        if (connection) {
            await new Promise((resolve) => {
                connection.close(() => resolve());
            });
        }
        
        console.log('\n=== Test Summary ===');
        console.log(`Passed: ${testResults.passed.length}`);
        console.log(`Failed: ${testResults.failed.length}`);
        console.log(`Not Supported: ${testResults.notSupported.length}`);
        
        if (testResults.notSupported.length > 0) {
            console.log('\n=== Not Supported Features ===');
            testResults.notSupported.forEach(r => {
                console.log(`- ${r.test}: ${r.message}`);
            });
        }
        
        if (testResults.failed.length > 0) {
            console.log('\n=== Failed Tests ===');
            testResults.failed.forEach(r => {
                console.log(`- ${r.test}: ${r.message}`);
            });
        }
        
        setTimeout(() => process.exit(0), 1000);
    });

    // 1. Connection Management Tests
    describe('Connection Management', function() {
        it('should create multiple connections', async function() {
            try {
                const conn2 = await new Promise((resolve, reject) => {
                    instance.reserve((err, conn) => {
                        if (err) reject(err);
                        else resolve(conn.conn);
                    });
                });
                await new Promise((resolve) => {
                    instance.release(conn2, () => resolve());
                });
                logResult('Multiple connections', 'pass');
            } catch (err) {
                logResult('Multiple connections', 'fail', err.message);
                throw err;
            }
        });

        it('should handle connection timeout - NOT SUPPORTED', async function() {
            logResult('Connection timeout configuration', 'not-supported', 
                'JDBC connection timeout is set at URL level, not per-connection');
        });

        it('should get active host info - NOT SUPPORTED', async function() {
            logResult('Get active host', 'not-supported', 
                'JDBC does not expose active host information like node-cubrid');
        });
    });

    // 2. Query Execution Tests
    describe('Query Execution', function() {
        it('should execute simple query', async function() {
            try {
                const statement = await new Promise((resolve, reject) => {
                    connection.createStatement((err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                const result = await new Promise((resolve, reject) => {
                    statement.executeQuery('SELECT 1', (err, rs) => {
                        if (err) reject(err);
                        else resolve(rs);
                    });
                });

                await new Promise((resolve) => {
                    result.close(() => resolve());
                });

                await new Promise((resolve) => {
                    statement.close(() => resolve());
                });

                logResult('Simple query execution', 'pass');
            } catch (err) {
                logResult('Simple query execution', 'fail', err.message);
                throw err;
            }
        });

        it('should execute query with parameters', async function() {
            try {
                const sql = 'SELECT * FROM nation WHERE continent = ?';
                const ps = await new Promise((resolve, reject) => {
                    connection.prepareStatement(sql, (err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                await new Promise((resolve, reject) => {
                    ps.setString(1, 'Asia', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                const result = await new Promise((resolve, reject) => {
                    ps.executeQuery((err, rs) => {
                        if (err) reject(err);
                        else resolve(rs);
                    });
                });

                await new Promise((resolve) => {
                    result.close(() => resolve());
                });

                await new Promise((resolve) => {
                    ps.close(() => resolve());
                });

                logResult('Parameterized query', 'pass');
            } catch (err) {
                logResult('Parameterized query', 'fail', err.message);
                throw err;
            }
        });

        it('should handle query handle management - NOT SUPPORTED', async function() {
            logResult('Query handle management', 'not-supported', 
                'JDBC uses Statement/ResultSet objects, not query handles like node-cubrid');
        });

        it('should execute multiple queries without explicit close - DIFFERENT', async function() {
            try {
                const statement = await new Promise((resolve, reject) => {
                    connection.createStatement((err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                // Execute multiple queries
                for (let i = 0; i < 3; i++) {
                    const result = await new Promise((resolve, reject) => {
                        statement.executeQuery('SELECT 1', (err, rs) => {
                            if (err) reject(err);
                            else resolve(rs);
                        });
                    });
                    // JDBC requires explicit close for each ResultSet
                    await new Promise((resolve) => {
                        result.close(() => resolve());
                    });
                }

                await new Promise((resolve) => {
                    statement.close(() => resolve());
                });

                logResult('Multiple queries require explicit ResultSet close', 'not-supported', 
                    'JDBC requires explicit ResultSet.close() for each query, node-cubrid auto-manages query handles');
            } catch (err) {
                logResult('Multiple queries', 'fail', err.message);
                throw err;
            }
        });
    });

    // 3. Transaction Tests
    describe('Transaction Management', function() {
        it('should commit transaction', async function() {
            try {
                await new Promise((resolve, reject) => {
                    connection.setAutoCommit(false, (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    connection.commit((err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    connection.setAutoCommit(true, (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                logResult('Transaction commit', 'pass');
            } catch (err) {
                logResult('Transaction commit', 'fail', err.message);
                throw err;
            }
        });

        it('should rollback transaction', async function() {
            try {
                await new Promise((resolve, reject) => {
                    connection.setAutoCommit(false, (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    connection.rollback((err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    connection.setAutoCommit(true, (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                logResult('Transaction rollback', 'pass');
            } catch (err) {
                logResult('Transaction rollback', 'fail', err.message);
                throw err;
            }
        });

        it('should get autocommit mode - NOT FULLY SUPPORTED', async function() {
            logResult('Get autocommit mode', 'not-supported', 
                'JDBC getAutoCommit() exists but requires callback conversion, node-cubrid provides sync getter');
        });
    });

    // 4. Protocol and Version Tests
    describe('Protocol and Version', function() {
        it('should get engine version - NOT SUPPORTED', async function() {
            logResult('Get engine version', 'not-supported', 
                'JDBC does not expose CUBRID engine version directly like node-cubrid.getEngineVersion()');
        });

        it('should use old query protocol - NOT SUPPORTED', async function() {
            logResult('Old query protocol', 'not-supported', 
                'JDBC does not expose protocol selection, node-cubrid allows setEnforceOldQueryProtocol()');
        });

        it('should get broker info - NOT SUPPORTED', async function() {
            logResult('Broker info', 'not-supported', 
                'JDBC does not expose broker information (protocolVersion, etc.) like node-cubrid');
        });
    });

    // 5. Data Type Tests
    describe('Data Types', function() {
        before(async function() {
            const statement = await new Promise((resolve, reject) => {
                connection.createStatement((err, stmt) => {
                    if (err) reject(err);
                    else resolve(stmt);
                });
            });

            await new Promise((resolve, reject) => {
                statement.execute('DROP TABLE IF EXISTS test_datatypes', (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            await new Promise((resolve) => {
                statement.close(() => resolve());
            });
        });

        it('should handle Date types', async function() {
            try {
                const statement = await new Promise((resolve, reject) => {
                    connection.createStatement((err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.execute(
                        'CREATE TABLE test_datatypes(d DATE, dt DATETIME, t TIME, ts TIMESTAMP)',
                        (err) => {
                            if (err) reject(err);
                            else resolve();
                        }
                    );
                });

                await new Promise((resolve) => {
                    statement.close(() => resolve());
                });

                logResult('Date/Time data types', 'pass');
            } catch (err) {
                logResult('Date/Time data types', 'fail', err.message);
                throw err;
            }
        });

        it('should handle LOB types - LIMITED SUPPORT', async function() {
            logResult('LOB types (BLOB/CLOB)', 'not-supported', 
                'JDBC LOB handling is different from node-cubrid lobRead/lobWrite methods');
        });

        it('should handle ENUM types', async function() {
            try {
                const statement = await new Promise((resolve, reject) => {
                    connection.createStatement((err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.execute('DROP TABLE IF EXISTS test_enum', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.execute(
                        "CREATE TABLE test_enum(color ENUM('red', 'blue', 'green'))",
                        (err) => {
                            if (err) reject(err);
                            else resolve();
                        }
                    );
                });

                await new Promise((resolve) => {
                    statement.close(() => resolve());
                });

                logResult('ENUM data type', 'pass');
            } catch (err) {
                logResult('ENUM data type', 'fail', err.message);
                throw err;
            }
        });
    });

    // 6. Schema and Metadata Tests
    describe('Schema and Metadata', function() {
        it('should get schema information - NOT SUPPORTED', async function() {
            logResult('Get schema (getSchema)', 'not-supported', 
                'JDBC uses DatabaseMetaData, node-cubrid provides getSchema() method');
        });

        it('should get database parameters - NOT SUPPORTED', async function() {
            logResult('Database parameters (getDatabaseParameter)', 'not-supported', 
                'JDBC does not expose CUBRID-specific database parameters like node-cubrid');
        });

        it('should set database parameters - NOT SUPPORTED', async function() {
            logResult('Set database parameters (setDatabaseParameter)', 'not-supported', 
                'JDBC does not expose CUBRID-specific database parameter setting');
        });
    });

    // 7. Batch Operations
    describe('Batch Operations', function() {
        it('should execute batch queries', async function() {
            try {
                const statement = await new Promise((resolve, reject) => {
                    connection.createStatement((err, stmt) => {
                        if (err) reject(err);
                        else resolve(stmt);
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.execute('DROP TABLE IF EXISTS test_batch', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.execute('CREATE TABLE test_batch(id INT)', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                // JDBC batch
                await new Promise((resolve, reject) => {
                    statement.addBatch('INSERT INTO test_batch VALUES(1)', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.addBatch('INSERT INTO test_batch VALUES(2)', (err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve, reject) => {
                    statement.executeBatch((err) => {
                        if (err) reject(err);
                        else resolve();
                    });
                });

                await new Promise((resolve) => {
                    statement.close(() => resolve());
                });

                logResult('Batch execution', 'pass');
            } catch (err) {
                logResult('Batch execution', 'fail', err.message);
                throw err;
            }
        });

        it('should use batchExecuteNoQuery - NOT SUPPORTED', async function() {
            logResult('batchExecuteNoQuery method', 'not-supported', 
                'node-cubrid batchExecuteNoQuery() method not available in JDBC, use addBatch/executeBatch instead');
        });
    });

    // 8. Connection Features
    describe('Connection Features', function() {
        it('should handle connection events - NOT SUPPORTED', async function() {
            logResult('Connection events', 'not-supported', 
                'node-cubrid emits events (connect, disconnect, error), JDBC uses callbacks only');
        });

        it('should access socket properties - NOT SUPPORTED', async function() {
            logResult('Socket access (_socket property)', 'not-supported', 
                'node-cubrid exposes _socket, JDBC does not expose underlying socket');
        });

        it('should manage query result sets - DIFFERENT', async function() {
            logResult('Query result set management', 'not-supported', 
                'node-cubrid uses _queryResultSets object, JDBC requires explicit ResultSet management');
        });

        it('should use typed parameters (executeWithTypedParams) - NOT SUPPORTED', async function() {
            logResult('Typed parameters method', 'not-supported', 
                'node-cubrid executeWithTypedParams() not available, JDBC uses setInt/setString/etc methods');
        });

        it('should fetch with queryAll - NOT SUPPORTED', async function() {
            logResult('queryAll method', 'not-supported', 
                'node-cubrid queryAll() method not available in JDBC');
        });

        it('should use fetch for cursor - NOT SUPPORTED', async function() {
            logResult('fetch method for cursor', 'not-supported', 
                'node-cubrid fetch() method not available, JDBC uses ResultSet.next()');
        });
    });
});
