<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Persistence;

use PDO;
use RuntimeException;

final class SqliteConnectionFactory
{
    public function create(string $databasePath): PDO
    {
        $databasePath = trim($databasePath);

        if ($databasePath === '') {
            throw new RuntimeException(
                'SQLite database path cannot be empty.'
            );
        }

        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            throw new RuntimeException(
                'SQLite database directory does not exist.'
            );
        }

        $pdo = new PDO(
            'sqlite:' . $databasePath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE
                    => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE
                    => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES
                    => false,
            ]
        );

        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        return $pdo;
    }
}
