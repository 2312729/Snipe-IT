import { spawnSync } from 'node:child_process';
import { constants, copyFileSync, existsSync, unlinkSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { generateKeyPairSync, randomBytes, randomUUID } from 'node:crypto';
import { MySqlContainer } from '@testcontainers/mysql';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const testingEnvironment = path.join(projectRoot, '.env.testing');
const testingEnvironmentExample = path.join(projectRoot, '.env.testing.example');
const xamppPhp = 'C:\\xampp\\php\\php.exe';
const phpBinary = process.env.PHP_BINARY || (
    process.platform === 'win32' && existsSync(xamppPhp) ? xamppPhp : 'php'
);

if (Number(process.versions.node.split('.')[0]) < 18) {
    throw new Error('Testcontainers requires Node.js 18 or newer.');
}

let createdTestingEnvironment = false;
let container;
let containerStarted = false;
let exitCode = 1;

try {
    if (!existsSync(testingEnvironment)) {
        if (!existsSync(testingEnvironmentExample)) {
            throw new Error(
                '.env.testing and .env.testing.example are both missing; refusing to run database integration tests.',
            );
        }

        try {
            copyFileSync(testingEnvironmentExample, testingEnvironment, constants.COPYFILE_EXCL);
            createdTestingEnvironment = true;
        } catch (error) {
            if (error.code !== 'EEXIST') {
                throw error;
            }
        }
    }

    const database = `snipeit_test_${randomUUID().replaceAll('-', '').slice(0, 12)}`;
    const username = 'snipeit_test';
    const password = randomBytes(24).toString('hex');
    const { privateKey, publicKey } = generateKeyPairSync('rsa', {
        modulusLength: 2048,
        privateKeyEncoding: {
            type: 'pkcs8',
            format: 'pem',
        },
        publicKeyEncoding: {
            type: 'spki',
            format: 'pem',
        },
    });
    const mysqlContainer = new MySqlContainer('mysql:8.0.36')
        .withDatabase(database)
        .withUsername(username)
        .withUserPassword(password);
    container = await mysqlContainer.start();
    containerStarted = true;

    const phpunit = path.join(projectRoot, 'vendor', 'bin', 'phpunit');
    const result = spawnSync(phpBinary, [phpunit, ...process.argv.slice(2)], {
        cwd: projectRoot,
        env: {
            ...process.env,
            APP_ENV: 'testing',
            DB_CONNECTION: 'mysql',
            DB_HOST: container.getHost(),
            DB_PORT: String(container.getPort()),
            DB_DATABASE: database,
            DB_USERNAME: username,
            DB_PASSWORD: password,
            PASSPORT_PRIVATE_KEY: privateKey,
            PASSPORT_PUBLIC_KEY: publicKey,
            TESTCONTAINERS_MYSQL: '1',
        },
        stdio: 'inherit',
    });

    if (result.error) {
        if (result.error.code === 'ENOENT') {
            throw new Error(
                `PHP CLI was not found at "${phpBinary}". Add PHP to PATH or set PHP_BINARY to the path of php.exe.`,
            );
        }

        throw result.error;
    }

    exitCode = result.status ?? (result.signal ? 1 : 0);
} finally {
    try {
        if (containerStarted) {
            await container.stop();
        }
    } finally {
        if (createdTestingEnvironment) {
            unlinkSync(testingEnvironment);
        }
    }
}

process.exitCode = exitCode;
