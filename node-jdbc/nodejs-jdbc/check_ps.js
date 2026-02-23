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
        
        const ps = await conn.prepareStatement("SELECT 1");
        console.log("ps type:", typeof ps);
        console.log("ps methods:", Object.keys(ps));
        console.log("ps prototype methods:", Object.getOwnPropertyNames(Object.getPrototypeOf(ps)));
        
        if (ps.close) {
            console.log("ps.close exists");
            await ps.close();
        } else {
            console.log("ps.close missing!");
        }
        
        await jdbc.release(connObj);
    } catch (e) {
        console.error(e);
    }
})();
