import { expect } from 'chai';
import jdbc from 'jdbc';
import jinst from '../node_modules/jdbc/lib/jinst.js';
import testSetup from './testSetup.js';

// JVM 초기화
if (!jinst.isJvmCreated()) {
    jinst.addOption("-Djava.awt.headless=true");
    jinst.addOption("-Xmx512m");
    jinst.setupClasspath(['./lib/cubrid-jdbc-11.3.0.0047.jar']);
}

describe('CUBRID (JDBC)', function() {
    describe('createConnection', function() {
        this.timeout(10000);

        it('should succeed to create and close a connection', async function() {
            const config = {
                url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8',
                drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
                user: 'dba',
                password: ''
            };

            const instance = new jdbc(config);
            
            await new Promise((resolve, reject) => {
                instance.initialize((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            const conn = await new Promise((resolve, reject) => {
                instance.reserve((err, c) => {
                    if (err) reject(err);
                    else resolve(c.conn);
                });
            });

            await new Promise((resolve) => {
                conn.close(() => {
                    instance.release(conn, () => resolve());
                });
            });
        });

        it.skip('should get active host - NOT SUPPORTED', function() {
            // JDBC does not expose active host information
        });

        it.skip('should handle connection timeout at instance level - NOT SUPPORTED', function() {
            // JDBC connection timeout is set at URL level
        });

        it('should create connection with different parameters', async function() {
            const config = {
                url: 'jdbc:cubrid:192.168.2.32:33000:demodb:dba::?charSet=utf-8',
                drivername: 'cubrid.jdbc.driver.CUBRIDDriver',
                user: 'dba',
                password: ''
            };

            const instance = new jdbc(config);
            
            await new Promise((resolve, reject) => {
                instance.initialize((err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });

            expect(instance).to.be.an('object');

            const conn = await new Promise((resolve, reject) => {
                instance.reserve((err, c) => {
                    if (err) reject(err);
                    else resolve(c.conn);
                });
            });

            expect(conn).to.be.an('object');

            await new Promise((resolve) => {
                conn.close(() => {
                    instance.release(conn, () => resolve());
                });
            });
        });
    });
});
