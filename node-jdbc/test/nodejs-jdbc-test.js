import { JDBC, isJvmCreated, addOption, setupClasspath } from 'nodejs-jdbc';
import { expect } from 'chai';
import winston from 'winston';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const insertCount = 200;

const logger = winston.createLogger({
    level: 'error',
    format: winston.format.simple(),
    transports: [
        new winston.transports.Console({
            level: 'error',
            format: winston.format.simple()
        })
    ]
});

global.logger = logger;
global.winston = winston;

// JVM 초기화 수정
if (!isJvmCreated()) {
    addOption("-Djava.awt.headless=true");
    addOption("-Xmx512m");
    // 라이브러리 경로 확인 필요 (상대 경로 조정)
    setupClasspath([path.resolve(__dirname, '../lib/cubrid-jdbc-11.3.0.0047.jar')]);
}

const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8&connectTimeout=30&queryTimeout=30',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    // user/password can be here or in properties
    minpoolsize: 1,
    maxpoolsize: 10,
    properties: {
        'user': 'dba',
        'password': ''
    },
    // nodejs-jdbc specific logging might differ, leaving these for compatibility if supported or ignored
    logging: {
        level: 'error',
        logger: logger,
        winston: winston
    },
    keepalive: {
        enabled: true,
        interval: 60000,
        query: 'SELECT 1'
    }
};

describe('CUBRID Node.js Performance Tests (nodejs-jdbc)', function() {
    let instance;
    let connectionObj; // Holds {uuid, conn}
    let connection;    // Holds conn
    let statement;

    this.timeout(100000);

    before(async function() {
        instance = new JDBC(config);
        
        await instance.initialize();
        console.log('initialize success');

        connectionObj = await instance.reserve();
        connection = connectionObj.conn;
        console.log('connection success');

        statement = await connection.createStatement();
        
        try {
            await statement.execute('DROP TABLE IF EXISTS test_table');
            await statement.execute(`
                CREATE TABLE IF NOT EXISTS test_table (
                    id INT,
                    name VARCHAR(100)
                )
            `);
        } catch (err) {
            console.error(err);
            throw err;
        }

        await connection.setAutoCommit(false);
    });

    after(async function() {
        if (statement) {
            console.log('statement close');
            // Check if close method exists and calls it
            await statement.close();
        }
        if (connectionObj) {
            console.log('connection close');
            // Usually we release the connection back to pool, or close it if we are done
            // nodejs-jdbc release expects the connection object wrapper
            await instance.release(connectionObj);
            console.log('instance release');
        }
        
        setTimeout(() => {
            process.exit(0);
        }, 1000);
    });

    it('insert work', async function() {
        const start = Date.now();
        const sql = 'INSERT INTO test_table VALUES(?, ?)';
        
        const preparedStatement = await connection.prepareStatement(sql);

        try {
            for (let i = 0; i < insertCount; i++) {
                await preparedStatement.setInt(1, i);
                await preparedStatement.setString(2, `한cunode${i}`);
                await preparedStatement.executeUpdate();
            }

            await connection.commit();
        } catch (err) {
            console.error('Insert error:', err);
            throw err;
        } finally {
            // nodejs-jdbc PreparedStatement does not expose close()
            // Try to access internal 'ps' object if possible or just log
            if (preparedStatement.ps && typeof preparedStatement.ps.closePromise === 'function') {
                await preparedStatement.ps.closePromise();
            } else {
               // console.log('preparedStatement.close not available');
            }
        }
        
        const end = Date.now();
        console.log(`insert work row : ${insertCount} elapsed time : ${end - start}ms`);
    });

    it('selected Count work', async function() {
        const sql = 'SELECT count(*) as "count" FROM test_table';
        const result = await statement.executeQuery(sql);
        
        // nodejs-jdbc uses toObjArray which returns a Promise resolving to array of objects
        const rows = await result.toObjArray();
        const count = rows[0].count || rows[0].COUNT; // Handle case sensitivity

        console.log(`selected Count work row : ${count}`);

        // Close result set if needed (depends on driver/wrapper implementation, explicit close is safer)
        // Note: result.close might not be exposed on the wrapper result object directly in all versions,
        // but typically ResultSet has .close().
        // If result is just the wrapper, check if it has close.
        if (result.close) {
             await result.close();
        } else if (result.resultSet && typeof result.resultSet.closePromise === 'function') {
             // Access internal resultSet if available (ResultSet wrapper has resultSet property)
             await result.resultSet.closePromise();
        }
    });

    it('selected work', async function() {
        const start = Date.now();
        let count = 0;
        const sql = 'SELECT * FROM test_table WHERE id = ?';
        
        const preparedStatement = await connection.prepareStatement(sql);

        try {
            for (let i = 0; i < insertCount; i++) {
                await preparedStatement.setInt(1, i);
                
                const result = await preparedStatement.executeQuery();
                const rows = await result.toObjArray();
                
                if (rows.length > 0) {
                     console.log(`id = ${rows[0].id || rows[0].ID} name = ${rows[0].name || rows[0].NAME}`);
                     count++;
                }

                if (result.close) {
                    await result.close();
                } else if (result.resultSet && typeof result.resultSet.closePromise === 'function') {
                     await result.resultSet.closePromise();
                }
            }
        } catch (err) {
            console.error('Select error:', err);
            throw err;
        } finally {
            if (preparedStatement.ps && typeof preparedStatement.ps.closePromise === 'function') {
                await preparedStatement.ps.closePromise();
            }
        }

        const end = Date.now();
        console.log(`selected work row : ${count} elapsed time : ${end - start}ms`);
    });
});
