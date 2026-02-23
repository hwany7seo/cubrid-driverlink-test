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
        
        // 1. Setup
        const stmt = await conn.createStatement();
        try { await stmt.executeUpdate("DROP TABLE IF EXISTS tbl_test_sql_rollback"); } catch(e) {}
        await stmt.executeUpdate("CREATE TABLE tbl_test_sql_rollback(id INT)");
        
        // 2. Transaction Start (AutoCommit false)
        await conn.setAutoCommit(false);
        
        // 3. Insert
        await stmt.executeUpdate("INSERT INTO tbl_test_sql_rollback VALUES(1)");
        
        // 4. Rollback via SQL
        console.log("Executing ROLLBACK via SQL...");
        // CUBRID supports 'ROLLBACK' or 'ROLLBACK WORK'
        await stmt.execute("ROLLBACK"); 
        
        // 5. Verify
        const rs = await stmt.executeQuery("SELECT count(*) FROM tbl_test_sql_rollback");
        while(await rs.next()) {
            console.log("Count after rollback:", await rs.getObject(1));
        }
        
        await stmt.executeUpdate("DROP TABLE tbl_test_sql_rollback");
        await stmt.close();
        await jdbc.release(connObj);
    } catch (e) {
        console.error(e);
    }
})();
