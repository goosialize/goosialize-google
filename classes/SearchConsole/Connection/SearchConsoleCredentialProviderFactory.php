<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

use Goosialize\Google\Core\Auth\ServiceAccountCredentialProvider;

final class SearchConsoleCredentialProviderFactory
{
    public const READONLY_SCOPE =
        'https://www.googleapis.com/auth/webmasters.readonly';

    public function create():
        ServiceAccountCredentialProvider
    {
        return new ServiceAccountCredentialProvider([
            self::READONLY_SCOPE,
        ]);
    }
}
