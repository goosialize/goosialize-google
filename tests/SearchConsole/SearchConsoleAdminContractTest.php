<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$files = [
    'classes/SearchConsole/SearchConsoleModule.php',
    'classes/Admin/SearchConsoleDashboardController.php',
    'classes/SearchConsole/Dashboard/SearchConsoleDashboardPeriod.php',
    'classes/SearchConsole/Dashboard/SearchConsoleDashboardService.php',
    'classes/SearchConsole/Dashboard/SearchConsolePropertyDirectory.php',
    'goosialize-google.php',
    'goosialize-google.yaml',
    'blueprints.yaml',
    'permissions.yaml',
];

foreach ($files as $relative) {
    if (
        !is_file(
            $root . '/' . $relative
        )
    ) {
        throw new RuntimeException(
            'Missing P9-C file: '
            . $relative
        );
    }
}

$plugin =
    file_get_contents(
        $root
        . '/goosialize-google.php'
    );

$controller =
    file_get_contents(
        $root
        . '/classes/Admin/'
        . 'SearchConsoleDashboardController.php'
    );

$permissions =
    file_get_contents(
        $root
        . '/permissions.yaml'
    );

$config =
    file_get_contents(
        $root
        . '/goosialize-google.yaml'
    );

$blueprint =
    file_get_contents(
        $root
        . '/blueprints.yaml'
    );

foreach (
    [
        $plugin,
        $controller,
        $permissions,
        $config,
        $blueprint,
    ] as $source
) {
    if ($source === false) {
        throw new RuntimeException(
            'Unable to read P9-C contract source.'
        );
    }
}

$checks = [
    [
        $plugin,
        '/goosialize-google/search-console/properties',
    ],
    [
        $plugin,
        '/goosialize-google/search-console/performance',
    ],
    [
        $controller,
        'api.goosialize_google.search_console.read',
    ],
    [
        $controller,
        'SearchConsoleCredentialProviderFactory',
    ],
    [
        $controller,
        'SearchConsoleDashboardService',
    ],
    [
        $permissions,
        'api.goosialize_google.search_console:',
    ],
    [
        $config,
        'search_console:',
    ],
    [
        $config,
        'default_property:',
    ],
    [
        $blueprint,
        'search_console.enabled:',
    ],
    [
        $blueprint,
        'search_console.default_property:',
    ],
];

foreach (
    $checks
    as [$source, $needle]
) {
    if (
        !str_contains(
            $source,
            $needle
        )
    ) {
        throw new RuntimeException(
            'P9-C contract missing: '
            . $needle
        );
    }
}

if (
    str_contains(
        $controller,
        'analytics.readonly'
    )
) {
    throw new RuntimeException(
        'Search Console controller must not request Analytics scope.'
    );
}

if (
    str_contains(
        $controller,
        'api.gpm.read'
    )
) {
    throw new RuntimeException(
        'Search Console Admin integration must not depend on api.gpm.read.'
    );
}

echo "SEARCH_CONSOLE_ADMIN_CONTRACT=PASS\n";
