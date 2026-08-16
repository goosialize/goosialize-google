<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$requiredFiles = [
    '/classes/Core/Persistence/StoragePathResolver.php',
    '/classes/Core/Persistence/MigrationRunner.php',
    '/classes/Core/Persistence/StorageBootstrapper.php',
    '/migrations/001_initial.sql',
];

foreach ($requiredFiles as $relative) {
    if (!is_file($root . $relative)) {
        throw new RuntimeException(
            'Missing storage bootstrap file: '
            . $relative
        );
    }
}

$runner =
    file_get_contents(
        $root
        . '/classes/Core/Persistence/MigrationRunner.php'
    );

$bootstrap =
    file_get_contents(
        $root
        . '/classes/Core/Persistence/StorageBootstrapper.php'
    );

$plugin =
    file_get_contents(
        $root
        . '/goosialize-google.php'
    );

foreach ([
    'schema_migrations',
    'beginTransaction',
    'rollBack',
    'gmdate',
    '*.sql',
] as $needle) {
    if (!str_contains($runner, $needle)) {
        throw new RuntimeException(
            'Migration contract missing: '
            . $needle
        );
    }
}

foreach ([
    'mkdir(',
    'SqliteConnectionFactory',
    'MigrationRunner',
    'storage.data_dir',
    'storage.database',
] as $needle) {
    if (!str_contains($bootstrap, $needle)) {
        throw new RuntimeException(
            'Bootstrap contract missing: '
            . $needle
        );
    }
}

if (
    !str_contains(
        $plugin,
        'new StorageBootstrapper('
    )
) {
    throw new RuntimeException(
        'Plugin startup does not bootstrap storage.'
    );
}

echo "P7_STORAGE_BOOTSTRAP_CONTRACT=PASS\n";
