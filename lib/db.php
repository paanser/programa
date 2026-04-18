<?php

declare(strict_types=1);

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('config.php no existe. Copia config.php.example y completa la conexión.');
    }

    $config = require $configPath;

    $host = (string)($config['db_host'] ?? 'localhost');
    $port = (string)($config['db_port'] ?? '3306');
    $dbName = (string)($config['db_name'] ?? '');
    $user = (string)($config['db_user'] ?? '');
    $pass = (string)($config['db_pass'] ?? '');

    if ($dbName === '' || $user === '') {
        throw new RuntimeException('Configuración de base de datos incompleta en config.php.');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}