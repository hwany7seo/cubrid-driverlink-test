import { JDBC, isJvmCreated, addOption, setupClasspath } from 'nodejs-jdbc';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

if (!isJvmCreated()) {
    addOption("-Djava.awt.headless=true");
    addOption("-Xmx512m");
    setupClasspath([path.resolve(__dirname, '../lib/cubrid-jdbc-11.3.0.0047.jar')]);
}

const config = {
    url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8',
    drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
    minpoolsize: 1,
    maxpoolsize: 5,
    properties: { user: 'dba', password: '' }
};

const jdbc = new JDBC(config);

(async () => {
    try {
        await jdbc.initialize();
        const connObj = await jdbc.reserve();
        const conn = connObj.conn;
        
        const stmt = await conn.createStatement();
        const rs = await stmt.executeQuery("SELECT 1 as num");
        
        console.log("rs.toObjArray exists?", typeof rs.toObjArray);
        
        if (rs.toObjArray) {
            try {
                const rows = await rs.toObjArray(); // Async?
                console.log("Result (async):", rows);
            } catch (e) {
                console.log("Async call failed, trying sync");
                const rows = rs.toObjArray(); // Sync?
                console.log("Result (sync):", rows);
            }
        }
        
        await stmt.close();
        await jdbc.release(connObj);
    } catch (e) {
        console.error(e);
    }
})();
