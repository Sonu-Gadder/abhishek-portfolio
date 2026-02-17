<?php

declare(strict_types=1);

function getEnvConfig(): array
{
    static $env = null;

    if ($env !== null) {
        return $env;
    }

    $envFile = __DIR__ . '/../.env';
    $env = parse_ini_file($envFile) ?: [];

    return $env;
}

function getPDO(): PDO
{
    $env = getEnvConfig();

    try {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $env['DB_HOST'] ?? 'localhost',
                $env['DB_NAME'] ?? ''
            ),
            $env['DB_USER'] ?? '',
            $env['DB_PASS'] ?? ''
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
}
