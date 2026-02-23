import jdbc from 'jdbc';
import jinst from '../node_modules/jdbc/lib/jinst.js';

if (!jinst.isJvmCreated()) {
    jinst.addOption("-Djava.awt.headless=true");
    jinst.addOption("-Xmx512m");
    jinst.setupClasspath(['./lib/cubrid-jdbc-11.3.0.0047.jar']);
}

const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:dba:public::?charSet=utf-8',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    minpoolsize: 1,
    maxpoolsize: 10,
    properties: {},
    user: 'dba',
    password: ''
};

class CUBRIDJDBCWrapper {
    constructor(config) {
        this.config = config;
        this.jdbc = new jdbc(config);
        this.conn = null;
        this.connObj = null;
    }

    async connect() {
        return new Promise((resolve, reject) => {
            this.jdbc.initialize((err) => {
                if (err) return reject(err);
                this.jdbc.reserve((err, connObj) => {
                    if (err) return reject(err);
                    this.conn = connObj.conn;
                    this.connObj = connObj;
                    resolve();
                });
            });
        });
    }

    async execute(sql) {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.createStatement((err, statement) => {
                if (err) return reject(err);
                statement.execute(sql, (err, result) => {
                    if (err) {
                        statement.close(() => {});
                        return reject(err);
                    }
                    statement.close((err) => {
                         if (err) console.error('Error closing statement:', err);
                         resolve(result);
                    });
                });
            });
        });
    }

    async query(sql) {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.createStatement((err, statement) => {
                if (err) return reject(err);
                statement.executeQuery(sql, (err, resultSet) => {
                    if (err) {
                        statement.close(() => {}); 
                        return reject(err);
                    }
                    resolve({ resultSet, statement });
                });
            });
        });
    }

    async closeQuery(resultSet, statement) {
        return new Promise((resolve, reject) => {
             if (resultSet && resultSet.close) {
                 resultSet.close((err) => {
                     if (err) console.error('Error closing RS:', err);
                     if (statement && statement.close) {
                         statement.close((err) => {
                             if (err) return reject(err);
                             resolve();
                         });
                     } else {
                         resolve();
                     }
                 });
             } else if (statement && statement.close) {
                 statement.close((err) => {
                     if (err) return reject(err);
                     resolve();
                 });
             } else {
                 resolve();
             }
        });
    }

    async setAutoCommitMode(autoCommit) {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.setAutoCommit(autoCommit, (err) => {
                if (err) reject(err);
                else resolve();
            });
        });
    }

    async commit() {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.commit((err) => {
                if (err) reject(err);
                else resolve();
            });
        });
    }

    async rollback() {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.rollback((err) => {
                if (err) reject(err);
                else resolve();
            });
        });
    }

    async createStatement() {
        if (!this.conn) throw new Error('Not connected');
        return new Promise((resolve, reject) => {
            this.conn.createStatement((err, statement) => {
                if (err) reject(err);
                else resolve(statement);
            });
        });
    }

    async prepareStatement(sql) {
         if (!this.conn) throw new Error('Not connected');
         return new Promise((resolve, reject) => {
             this.conn.prepareStatement(sql, (err, ps) => {
                 if (err) reject(err);
                 else resolve(ps);
             });
         });
    }

    async close() {
        if (this.connObj) {
            return new Promise((resolve, reject) => {
                this.jdbc.release(this.connObj, (err) => {
                    if (err) return reject(err);
                    this.conn = null;
                    this.connObj = null;
                    resolve();
                });
            });
        }
    }
}

function createDefaultCUBRIDDemodbConnection() {
    return new CUBRIDJDBCWrapper(config);
}

function cleanup(tableName) {
    return async function() {
        if (this && this.timeout) this.timeout(20000);
        const client = createDefaultCUBRIDDemodbConnection();
        try {
            await client.connect();
            await client.execute(`DROP TABLE IF EXISTS ${tableName}`);
        } catch (e) {
            // console.error('Cleanup warning:', e);
        } finally {
            await client.close();
        }
    };
}

export default {
    jinst,
    config,
    createDefaultCUBRIDDemodbConnection,
    cleanup
};
