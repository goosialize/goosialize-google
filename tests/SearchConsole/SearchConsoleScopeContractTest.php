<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$provider = file_get_contents(
    $root
    . '/classes/Core/Auth/'
    . 'ServiceAccountCredentialProvider.php'
);

if ($provider === false) {
    throw new RuntimeException(
        'Unable to read credential provider.'
    );
}

if (
    !str_contains(
        $provider,
        'analytics.readonly'
    )
) {
    throw new RuntimeException(
        'Backward-compatible Analytics readonly scope missing.'
    );
}

if (
    !str_contains(
        $provider,
        'private array $scopes'
    )
) {
    throw new RuntimeException(
        'Credential provider is not scope-aware.'
    );
}

$factory = file_get_contents(
    $root
    . '/classes/SearchConsole/Connection/'
    . 'SearchConsoleCredentialProviderFactory.php'
);

if ($factory === false) {
    throw new RuntimeException(
        'Unable to read Search Console credential factory.'
    );
}

if (
    !str_contains(
        $factory,
        'webmasters.readonly'
    )
) {
    throw new RuntimeException(
        'Search Console readonly scope missing.'
    );
}

if (
    str_contains(
        $factory,
        'analytics.readonly'
    )
) {
    throw new RuntimeException(
        'Search Console credential factory must not request Analytics scope.'
    );
}

echo "SEARCH_CONSOLE_SCOPE_CONTRACT=PASS\n";
