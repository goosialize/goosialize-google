<?php

declare(strict_types=1);

$schemaPath = __DIR__
    . '/../../migrations/001_initial.sql';

$schema = file_get_contents($schemaPath);

if ($schema === false) {
    throw new RuntimeException(
        'Unable to read initial SQLite schema.'
    );
}

$db = new PDO('sqlite::memory:');

$db->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

$db->setAttribute(
    PDO::ATTR_DEFAULT_FETCH_MODE,
    PDO::FETCH_ASSOC
);

$db->exec('PRAGMA foreign_keys = ON');
$db->exec($schema);

$expectedTables = [
    'analytics_properties',
    'google_connections',
    'report_cache',
    'schema_migrations',
    'sync_runs',
];

$query = $db->query(
    "SELECT name
       FROM sqlite_master
      WHERE type = 'table'
        AND name NOT LIKE 'sqlite_%'
      ORDER BY name"
);

$actualTables = $query->fetchAll(
    PDO::FETCH_COLUMN
);

if ($actualTables !== $expectedTables) {
    fwrite(
        STDERR,
        'EXPECTED_TABLES='
        . json_encode($expectedTables)
        . PHP_EOL
    );

    fwrite(
        STDERR,
        'ACTUAL_TABLES='
        . json_encode($actualTables)
        . PHP_EOL
    );

    exit(1);
}

$db->exec("
    INSERT INTO google_connections (
        id,
        label,
        credential_type,
        credential_reference,
        enabled,
        created_at,
        updated_at
    ) VALUES (
        'primary',
        'Primary Google',
        'service_account_file',
        '/secure/google/account.json',
        1,
        '2026-08-15T00:00:00Z',
        '2026-08-15T00:00:00Z'
    )
");

$db->exec("
    INSERT INTO analytics_properties (
        property_id,
        connection_id,
        display_name,
        enabled,
        created_at,
        updated_at
    ) VALUES (
        '123456789',
        'primary',
        'Example Property',
        1,
        '2026-08-15T00:00:00Z',
        '2026-08-15T00:00:00Z'
    )
");

$db->exec("
    INSERT INTO report_cache (
        cache_key,
        property_id,
        report_type,
        request_hash,
        payload_json,
        fetched_at,
        expires_at
    ) VALUES (
        'cache-1',
        '123456789',
        'overview',
        'hash-1',
        '{}',
        '2026-08-15T00:00:00Z',
        '2026-08-15T00:15:00Z'
    )
");

$db->exec("
    INSERT INTO sync_runs (
        id,
        property_id,
        operation,
        status,
        started_at,
        finished_at
    ) VALUES (
        'sync-1',
        '123456789',
        'overview',
        'success',
        '2026-08-15T00:00:00Z',
        '2026-08-15T00:00:01Z'
    )
");

$count = (int) $db
    ->query(
        'SELECT COUNT(*)
           FROM report_cache'
    )
    ->fetchColumn();

if ($count !== 1) {
    throw new RuntimeException(
        'Report cache insert contract failed.'
    );
}

$foreignKeys = (int) $db
    ->query('PRAGMA foreign_keys')
    ->fetchColumn();

if ($foreignKeys !== 1) {
    throw new RuntimeException(
        'SQLite foreign keys are not enabled.'
    );
}

echo "P2_SQLITE_CONTRACT=PASS\n";
