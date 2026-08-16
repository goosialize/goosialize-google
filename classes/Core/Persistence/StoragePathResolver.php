<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Persistence;

use Grav\Common\Grav;
use RuntimeException;

final class StoragePathResolver
{
    public function __construct(
        private readonly Grav $grav
    ) {
    }

    public function dataDirectory(
        string $configuredPath
    ): string {
        $configuredPath =
            trim($configuredPath);

        if ($configuredPath === '') {
            throw new RuntimeException(
                'Storage data directory cannot be empty.'
            );
        }

        if (
            str_starts_with(
                $configuredPath,
                'user/data/'
            )
        ) {
            $relative =
                substr(
                    $configuredPath,
                    strlen('user/data/')
                );

            $base =
                $this->userDataDirectory();

            return rtrim(
                $base,
                DIRECTORY_SEPARATOR
            )
                . DIRECTORY_SEPARATOR
                . ltrim(
                    $relative,
                    DIRECTORY_SEPARATOR
                );
        }

        if (
            str_starts_with(
                $configuredPath,
                'user://data/'
            )
        ) {
            $relative =
                substr(
                    $configuredPath,
                    strlen('user://data/')
                );

            $base =
                $this->userDataDirectory();

            return rtrim(
                $base,
                DIRECTORY_SEPARATOR
            )
                . DIRECTORY_SEPARATOR
                . ltrim(
                    $relative,
                    DIRECTORY_SEPARATOR
                );
        }

        if ($this->absolute($configuredPath)) {
            return $configuredPath;
        }

        throw new RuntimeException(
            'Storage data directory must use user/data/, user://data/, or an absolute path.'
        );
    }

    public function databasePath(
        string $configuredDirectory,
        string $database
    ): string {
        $database =
            trim($database);

        if (
            $database === ''
            || basename($database) !== $database
        ) {
            throw new RuntimeException(
                'SQLite database must be a filename without directory traversal.'
            );
        }

        return rtrim(
            $this->dataDirectory(
                $configuredDirectory
            ),
            DIRECTORY_SEPARATOR
        )
            . DIRECTORY_SEPARATOR
            . $database;
    }

    private function userDataDirectory(): string
    {
        $locator =
            $this->grav['locator']
            ?? null;

        if (
            !is_object($locator)
            || !method_exists(
                $locator,
                'findResource'
            )
        ) {
            throw new RuntimeException(
                'Grav locator service is unavailable.'
            );
        }

        $path =
            $locator->findResource(
                'user://data',
                true,
                true
            );

        if (
            !is_string($path)
            || $path === ''
        ) {
            throw new RuntimeException(
                'Grav user data directory could not be resolved.'
            );
        }

        return $path;
    }

    private function absolute(
        string $path
    ): bool {
        return str_starts_with(
            $path,
            DIRECTORY_SEPARATOR
        )
            || preg_match(
                '/^[A-Za-z]:[\\\\\\/]/',
                $path
            ) === 1;
    }
}
