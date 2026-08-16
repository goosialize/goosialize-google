<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Persistence;

use PDO;
use RuntimeException;
use Throwable;

final class MigrationRunner
{
    public function migrate(
        PDO $pdo,
        string $migrationDirectory
    ): int {
        if (!is_dir($migrationDirectory)) {
            throw new RuntimeException(
                'Migration directory does not exist.'
            );
        }

        $files =
            glob(
                rtrim(
                    $migrationDirectory,
                    DIRECTORY_SEPARATOR
                )
                . DIRECTORY_SEPARATOR
                . '*.sql'
            );

        if ($files === false) {
            throw new RuntimeException(
                'Unable to enumerate migrations.'
            );
        }

        sort(
            $files,
            SORT_STRING
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version TEXT PRIMARY KEY NOT NULL,
                applied_at TEXT NOT NULL
            )'
        );

        $applied =
            $pdo->prepare(
                'SELECT 1
                 FROM schema_migrations
                 WHERE version = :version
                 LIMIT 1'
            );

        $record =
            $pdo->prepare(
                'INSERT INTO schema_migrations (
                    version,
                    applied_at
                ) VALUES (
                    :version,
                    :applied_at
                )'
            );

        $count = 0;

        foreach ($files as $file) {
            $version =
                basename(
                    $file,
                    '.sql'
                );

            if (
                $version === ''
                || preg_match(
                    '/^[A-Za-z0-9._-]+$/',
                    $version
                ) !== 1
            ) {
                throw new RuntimeException(
                    'Invalid migration filename.'
                );
            }

            $applied->execute([
                'version' => $version,
            ]);

            if ($applied->fetchColumn()) {
                continue;
            }

            $sql =
                file_get_contents(
                    $file
                );

            if (
                !is_string($sql)
                || trim($sql) === ''
            ) {
                throw new RuntimeException(
                    'Migration SQL is empty: '
                    . $version
                );
            }

            $pdo->beginTransaction();

            try {
                $pdo->exec($sql);

                $record->execute([
                    'version' =>
                        $version,
                    'applied_at' =>
                        gmdate(
                            'Y-m-d\TH:i:s\Z'
                        ),
                ]);

                $pdo->commit();

                $count++;
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw new RuntimeException(
                    'Migration failed: '
                    . $version,
                    0,
                    $error
                );
            }
        }

        return $count;
    }
}
