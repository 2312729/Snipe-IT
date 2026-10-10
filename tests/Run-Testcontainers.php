<?php

declare(strict_types=1);

$mysqlContainerClass = 'Testcontainers\\Container\\MySQLContainer';

$projectRoot = dirname(__DIR__);
$testingEnvironment = $projectRoot.'/.env.testing';
$temporaryTestingEnvironment = false;
$container = null;
$exitCode = 1;

try {
    if (! extension_loaded('pdo_mysql')) {
        throw new RuntimeException('The PDO MySQL extension is required to verify the container connection.');
    }

    if (! class_exists($mysqlContainerClass)) {
        throw new RuntimeException(
            'MySQLContainer is unavailable; install testcontainers/testcontainers-php 1.1.0 with Composer.'
        );
    }

    if (! is_file($testingEnvironment)) {
        $testingEnvironmentExample = $projectRoot.'/.env.testing.example';

        if (! is_file($testingEnvironmentExample)) {
            throw new RuntimeException(
                '.env.testing and .env.testing.example are both missing; refusing to start integration tests.'
            );
        }

        if (! copy($testingEnvironmentExample, $testingEnvironment)) {
            throw new RuntimeException('Could not create temporary .env.testing from .env.testing.example.');
        }

        $temporaryTestingEnvironment = true;
    }

    $database = 'snipeit_test_'.bin2hex(random_bytes(6));
    $username = 'snipeit_test';
    $password = bin2hex(random_bytes(24));

    $container = (new $mysqlContainerClass('mysql:8.0.36'))
        ->withDatabase($database)
        ->withUsername($username)
        ->withUserPassword($password);
    $container->start();

    $host = $container->getHost();
    $port = $container->getMappedPort(3306);
    $pdoHost = str_contains($host, ':') && ! str_starts_with($host, '[')
        ? '['.$host.']'
        : $host;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $pdoHost, $port, $database);
    $deadline = microtime(true) + 60;
    $pdo = null;
    $lastConnectionException = null;

    while (microtime(true) < $deadline) {
        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
            ]);
            break;
        } catch (PDOException $exception) {
            $lastConnectionException = $exception;
            usleep(500_000);
        }
    }

    if (! $pdo instanceof PDO) {
        throw new RuntimeException(
            'MySQL did not become ready within 60 seconds.',
            previous: $lastConnectionException
        );
    }

    $environment = getenv();
    $environment = is_array($environment) ? $environment : [];
    $environment['APP_ENV'] = 'testing';
    $environment['DB_CONNECTION'] = 'mysql';
    $environment['DB_HOST'] = $host;
    $environment['DB_PORT'] = (string) $port;
    $environment['DB_DATABASE'] = $database;
    $environment['DB_USERNAME'] = $username;
    $environment['DB_PASSWORD'] = $password;
    $environment['TESTCONTAINERS_MYSQL'] = '1';

    $phpunit = $projectRoot.'/vendor/bin/phpunit';

    if (! is_file($phpunit)) {
        throw new RuntimeException('PHPUnit is missing; install Composer development dependencies first.');
    }

    $command = [PHP_BINARY, $phpunit, ...array_slice($argv, 1)];
    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes, $projectRoot, $environment);

    if (! is_resource($process)) {
        throw new RuntimeException('Could not start PHPUnit.');
    }

    $exitCode = proc_close($process);
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf("Testcontainers integration run failed: %s\n", $exception->getMessage()));
    $exitCode = 1;
} finally {
    if ($container !== null) {
        try {
            $container->stop();
        } catch (Throwable $exception) {
            fwrite(STDERR, sprintf("Failed to stop the MySQL test container: %s\n", $exception->getMessage()));
            $exitCode = 1;
        }
    }

    if ($temporaryTestingEnvironment && is_file($testingEnvironment) && ! unlink($testingEnvironment)) {
        fwrite(STDERR, "Failed to remove temporary .env.testing.\n");
        $exitCode = 1;
    }
}

exit($exitCode);
