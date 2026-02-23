import { JDBC, isJvmCreated, addOption, setupClasspath } from 'nodejs-jdbc';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// 1. JVM Setup (One-time initialization for nodejs-jdbc)
if (!isJvmCreated()) {
    addOption("-Djava.awt.headless=true");
    addOption("-Xmx512m");
    setupClasspath([path.resolve(__dirname, '../../lib/cubrid-jdbc-11.3.0.0047.jar')]);
}

// 2. Configuration (Target: 192.168.2.32, User: dba)
const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    minpoolsize: 1,
    maxpoolsize: 5,
    properties: {
        user: 'dba',
        password: ''
    }
};

// Singleton JDBC instance to share connection pool across tests
const jdbcInstance = new JDBC(config);

// 3. Wrapper to map node-cubrid API to nodejs-jdbc API
class CUBRIDAsyncWrapper {
    constructor(cfg = config) {
        this.config = cfg;
        this.jdbc = jdbcInstance; // Use shared instance
        this.connObj = null;
        this.conn = null;
        
        // Mock properties
        this.brokerInfo = { protocolVersion: 0 }; 
        this._queryResultSets = {}; 
    }

    async connect() {
        if (!this.connObj) {
            await this.jdbc.initialize();
            this.connObj = await this.jdbc.reserve();
            this.conn = this.connObj.conn;
        }
    }

    async close() {
        if (this.connObj) {
            try {
                // Try to rollback any active transaction before releasing to pool
                // This prevents returning a connection with active transaction to the pool
                if (this.conn) {
                    try { await this.conn.rollback(); } catch(e) {}
                    try { await this.conn.setAutoCommit(true); } catch(e) {} // Reset to autoCommit
                }
            } catch (e) {
                // Ignore cleanup errors
            }
            
            await this.jdbc.release(this.connObj);
            this.connObj = null;
            this.conn = null;
        }
    }

    async execute(sql) {
        if (!this.conn) throw new Error('Not connected');
        const stmt = await this.conn.createStatement();
        try {
            return await stmt.executeUpdate(sql);
        } finally {
            await stmt.close();
        }
    }

    async query(sql, params) {
        if (!this.conn) throw new Error('Not connected');
        
        // If params provided, use PreparedStatement
        if (params && Array.isArray(params)) {
            const ps = await this.conn.prepareStatement(sql);
            try {
                for (let i = 0; i < params.length; i++) {
                    const val = params[i];
                    if (typeof val === 'number') await ps.setInt(i + 1, val);
                    else await ps.setString(i + 1, String(val));
                }
                const rs = await ps.executeQuery();
                return await this._processResultSet(rs);
            } finally {
                await ps.close();
            }
        } else {
            // Simple Statement
            const stmt = await this.conn.createStatement();
            try {
                const rs = await stmt.executeQuery(sql);
                return await this._processResultSet(rs);
            } finally {
                await stmt.close();
            }
        }
    }
    
    async queryAll(sql, params) {
        return this.query(sql, params);
    }

    async _processResultSet(rs) {
        const rows = rs.toObjArray(); 
        const normalized = rows.map(row => {
            const newRow = {};
            for (const key in row) newRow[key.toUpperCase()] = row[key];
            return newRow;
        });
        
        const queryHandle = Math.floor(Math.random() * 10000);
        this._queryResultSets[queryHandle] = true;

        // Mimic node-cubrid result structure
        return { 
            result: {
                RowsCount: normalized.length,
                ColumnValues: normalized.map(r => Object.values(r)), // For tests checking ColumnValues
                ColumnNames: normalized.length > 0 ? Object.keys(normalized[0]) : [],
                // Helper for easier testing (not in original node-cubrid but useful)
                rows: normalized 
            },
            queryHandle: queryHandle 
        };
    }

    async batchExecuteNoQuery(sqls) {
        if (!this.conn) throw new Error('Not connected');
        const stmt = await this.conn.createStatement();
        try {
            if (!Array.isArray(sqls)) {
                throw new Error('Argument must be an array of SQL strings');
            }
            
            for (const sql of sqls) {
                 await stmt.addBatch(sql);
            }
            const counts = await stmt.executeBatch();
            
            return {
                queryHandle: Math.floor(Math.random() * 10000),
                result: {
                    RowsCount: counts.reduce((a, b) => a + (b > 0 ? b : 0), 0),
                    ColumnValues: [] 
                }
            };
        } finally {
             await stmt.close();
        }
    }

    async getEngineVersion() {
        if (!this.conn) throw new Error('Not connected');
        const meta = await this.conn.getMetaData();
        const name = await meta.getDatabaseProductName();
        const ver = await meta.getDatabaseProductVersion();
        return `${name} ${ver}`;
    }

    async setAutoCommit(autoCommit) {
        if (this.conn) {
            await this.conn.setAutoCommit(Boolean(autoCommit));
            try {
                // Verify status
                const ac = await this.conn.getAutoCommit();
                if (ac !== Boolean(autoCommit)) {
                   // Retry once if failed
                   await this.conn.setAutoCommit(Boolean(autoCommit));
                }
            } catch(e) {}
        }
    }

    async commit() {
        if (this.conn) await this.conn.commit();
    }

    async rollback() {
        if (this.conn) {
            try {
                await this.conn.rollback();
            } catch (e) {
                // Check for generic error message wrapping the cause
                // CUBRID JDBC throws UnsupportedOperationException if autoCommit is true
                if (e.message && (e.message.includes('auto-commit') || e.message.includes('UnsupportedOperationException'))) return;
                throw e;
            }
        }
    }
    
    async beginTransaction() {
        await this.setAutoCommit(false);
    }
    
    setEnforceOldQueryProtocol() {}
    
    getRawConnection() {
        return this.conn;
    }
    
    async prepareStatement(sql) {
        if (!this.conn) throw new Error('Not connected');
        const ps = await this.conn.prepareStatement(sql);
        // Return a wrapper that mimics node-cubrid usage in tests or provides standard JDBC async methods
        return {
            setInt: async (idx, val) => ps.setInt(idx, val),
            setString: async (idx, val) => ps.setString(idx, val),
            executeUpdate: async () => ps.executeUpdate(),
            executeQuery: async () => {
                const rs = await ps.executeQuery();
                // Add helper to fetch results
                rs.toObjArrayAsync = async () => this._processResultSet(rs);
                rs.closeAsync = async () => { if (rs.close) await rs.close(); };
                return rs;
            },
            close: async () => { if (ps.close) await ps.close(); }
        };
    }
}

// 4. Exports
export { config };
// Alias for tests using defaultConfig
export const defaultConfig = config;

// Track active clients to ensure cleanup
const activeClients = [];

export function createDefaultCUBRIDDemodbConnection() {
    const client = new CUBRIDAsyncWrapper(config);
    activeClients.push(client);
    return client;
}

export function cleanup(tableName) {
    return async function() {
        if (this && this.timeout) this.timeout(20000);
        
        // Close all active clients first to release locks
        while (activeClients.length > 0) {
            const client = activeClients.pop();
            try { 
                await Promise.race([
                    client.close(),
                    new Promise((_, reject) => setTimeout(() => reject(new Error('Close timeout')), 1000))
                ]);
            } catch(e) {}
        }
        
        const client = new CUBRIDAsyncWrapper(config);
        try {
            await client.connect();
            await client.execute(`DROP TABLE IF EXISTS ${tableName}`);
        } catch (e) {
            // Ignore
        } finally {
            try { await client.close(); } catch(e) {}
        }
    };
}

export { CUBRIDAsyncWrapper };
export default { config, defaultConfig, createDefaultCUBRIDDemodbConnection, cleanup, CUBRIDAsyncWrapper };
