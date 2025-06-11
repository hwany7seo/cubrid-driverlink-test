import jdbc from 'jdbc';
import { expect } from 'chai';
import jinst from '../node_modules/jdbc/lib/jinst.js';
import winston from 'winston';

const insertCount = 100;

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

if (!jinst.isJvmCreated()) {
    jinst.addOption("-Xrs");
    jinst.addOption("-XX:+UseG1GC");
    jinst.addOption("-XX:MaxGCPauseMillis=200");
    jinst.setupClasspath(['./lib/cubrid-jdbc-11.3.0.0047.jar']);
}

const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8&connectTimeout=30&queryTimeout=30',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    user: 'dba',
    password: '',
    minpoolsize: 0,
    maxpoolsize: 10,
    properties: {
        'user': 'dba',
        'password': ''
    },
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

describe('CUBRID Node.js Performance Tests', function() {
    let instance;
    let connection;
    let statement;

    this.timeout(100000);

    before(async function() {
        instance = new jdbc(config);
        
        await new Promise((resolve, reject) => {
            instance.initialize((err) => {
                if (err) {
                    console.error('initialize error:', err);
                    reject(err);
                } else {
                    console.log('initialize success');
                    resolve();
                }
            });
        });
        
        connection = await new Promise((resolve, reject) => {
            instance.reserve((err, conn) => {
                if (err) {
                    console.error('connection error:', err);
                    reject(err);
                } else {
                    console.log('connection success');
                    resolve(conn.conn);
                }
            });
        });
        
        statement = await new Promise((resolve, reject) => {
            connection.createStatement((err, stmt) => {
                if (err) reject(err);
                else resolve(stmt);
            });
        });
        
        await new Promise((resolve, reject) => {
            statement.execute('DROP TABLE IF EXISTS test_table', (err) => {
                if (err) reject(err);
                else resolve();
            });
        });

        await new Promise((resolve, reject) => {
            statement.execute(`
                CREATE TABLE IF NOT EXISTS test_table (
                    id INT,
                    name VARCHAR(100)
                )
            `, (err) => {
                if (err) reject(err);
                else resolve();
            });
        });
        
        await new Promise((resolve, reject) => {
            connection.setAutoCommit(false, (err) => {
                if (err) reject(err);
                else resolve();
            });
        });
    });

    after(async function() {
        if (statement) {
            await new Promise((resolve, reject) => {
                console.log('statement close');
                statement.close((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        }
        if (connection) {
            await new Promise((resolve, reject) => {
                console.log('connection close');
                connection.close((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
            await new Promise((resolve, reject) => {
                console.log('instance release');
                instance.release(connection, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        }
        
        setTimeout(() => {
            process.exit(0);
        }, 1000);
    });

    it('insert work', async function() {
        const start = Date.now();
        const sql = 'INSERT INTO test_table VALUES(?, ?)';
        
        const preparedStatement = await new Promise((resolve, reject) => {
            connection.prepareStatement(sql, (err, ps) => {
                if (err) reject(err);
                else resolve(ps);
            });
        });

        for (let i = 0; i < insertCount; i++) {
            await new Promise((resolve, reject) => {
                preparedStatement.setInt(1, i, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            await new Promise((resolve, reject) => {
                preparedStatement.setString(2, `cunode${i}`, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            await new Promise((resolve, reject) => {
                preparedStatement.executeUpdate((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        }

        await new Promise((resolve, reject) => {
            connection.commit((err) => {
                if (err) reject(err);
                else resolve();
            });
        });

        await new Promise((resolve, reject) => {
            preparedStatement.close((err) => {
                if (err) reject(err);
                else resolve();
            });
        });
        
        const end = Date.now();
        console.log(`insert work row : ${insertCount} elapsed time : ${end - start}ms`);
    });

    it('selected Count work', async function() {
        const result = await new Promise((resolve, reject) => {
            statement.executeQuery('SELECT count(*) as "count" FROM test_table', (err, rs) => {
                if (err) reject(err);
                else resolve(rs);
            });
        });

        const count = await new Promise((resolve, reject) => {
            result.toObject((err, data) => {
                if (err) reject(err);
                else resolve(data.rows[0].count);
            });
        });

        console.log(`selected Count work row : ${count}`);

        await new Promise((resolve, reject) => {
            result.close((err) => {
                if (err) reject(err);
                else resolve();
            });
        });
    });

    it('selected work', async function() {
        const start = Date.now();
        let count = 0;
        const sql = 'SELECT * FROM test_table WHERE id = ?';
        
        const preparedStatement = await new Promise((resolve, reject) => {
            connection.prepareStatement(sql, (err, ps) => {
                if (err) reject(err);
                else resolve(ps);
            });
        });

        for (let i = 0; i < insertCount; i++) {
            await new Promise((resolve, reject) => {
                preparedStatement.setInt(1, i, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            const result = await new Promise((resolve, reject) => {
                preparedStatement.executeQuery((err, rs) => {
                    if (err) reject(err);
                    else resolve(rs);
                });
            });
            
            const hasNext = await new Promise((resolve, reject) => {
                result.toObject((err, data) => {
                    if (err) reject(err);
                    else resolve(data.rows.length > 0);
                });
            });

            if (hasNext) {
                count++;
            }

            await new Promise((resolve, reject) => {
                result.close((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        }

        await new Promise((resolve, reject) => {
            preparedStatement.close((err) => {
                if (err) reject(err);
                else resolve();
            });
        });

        const end = Date.now();
        console.log(`selected work row : ${count} elapsed time : ${end - start}ms`);
    });
});
