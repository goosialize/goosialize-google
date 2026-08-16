<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Persistence;

use Grav\Common\Config\Config;
use Grav\Common\Grav;
use PDO;
use RuntimeException;

final class StorageBootstrapper
{
    public function __construct(
        private readonly Grav $grav,
        private readonly Config $config
    ) {
    }

    public function bootstrap(
        string $pluginDirectory
    ): PDO {
        $resolver =
            new StoragePathResolver(
                $this->grav
            );

        $dataDirectory =
            $resolver->dataDirectory(
                (string) $this->config->get(
                    'plugins.goosialize-google.storage.data_dir',
                    'user/data/goosialize-google'
                )
            );

        if (
            !is_dir($dataDirectory)
            && !mkdir(
                $dataDirectory,
                0750,
                true
            )
            && !is_dir($dataDirectory)
        ) {
            throw new RuntimeException(
                'Unable to create Goosialize Google data directory.'
            );
        }

        $databasePath =
            $resolver->databasePath(
                (string) $this->config->get(
                    'plugins.goosialize-google.storage.data_dir',
                    'user/data/goosialize-google'
                ),
                (string) $this->config->get(
                    'plugins.goosialize-google.storage.database',
                    'google.sqlite'
                )
            );

        $pdo =
            (new SqliteConnectionFactory())
                ->create(
                    $databasePath
                );

        (new MigrationRunner())
            ->migrate(
                $pdo,
                rtrim(
                    $pluginDirectory,
                    DIRECTORY_SEPARATOR
                )
                    . DIRECTORY_SEPARATOR
                    . 'migrations'
            );

        return $pdo;
    }
}
