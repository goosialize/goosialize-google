<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$required = [
    'permissions.yaml',
    'goosialize-google.php',
    'classes/Admin/AnalyticsDashboardController.php',
    'admin-next/pages/goosialize-google.js',
];

foreach (
    $required
    as $relative
) {
    if (
        !is_file(
            $root . '/' . $relative
        )
    ) {
        throw new RuntimeException(
            'Missing Admin2 file: '
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
        . '/classes/Admin/AnalyticsDashboardController.php'
    );

$page =
    file_get_contents(
        $root
        . '/admin-next/pages/goosialize-google.js'
    );

$permissions =
    file_get_contents(
        $root
        . '/permissions.yaml'
    );

foreach (
    [
        $plugin,
        $controller,
        $page,
        $permissions,
    ] as $text
) {
    if ($text === false) {
        throw new RuntimeException(
            'Unable to read Admin2 contract source.'
        );
    }
}

$checks = [
    [
        $plugin,
        'onApiRegisterRoutes',
    ],
    [
        $plugin,
        '/goosialize-google/properties',
    ],
    [
        $plugin,
        '/goosialize-google/analytics',
    ],
    [
        $plugin,
        "'page_type' =>",
    ],
    [
        $plugin,
        "'component'",
    ],
    [
        $controller,
        "getAttribute(\n                'api_user'",
    ],
    [
        $controller,
        'api.goosialize_google.analytics.read',
    ],
    [
        $permissions,
        'api.goosialize_google.analytics:',
    ],
    [
        $page,
        'window.__GRAV_PAGE_TAG',
    ],
    [
        $page,
        '/goosialize-google/properties',
    ],
    [
        $page,
        '/goosialize-google/analytics',
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
            'Admin2 contract missing: '
            . $needle
        );
    }
}

if (
    str_contains(
        $plugin,
        'api.gpm.read'
    )
    || str_contains(
        $controller,
        'api.gpm.read'
    )
    || str_contains(
        $page,
        'api.gpm.read'
    )
) {
    throw new RuntimeException(
        'Admin2 dashboard must not depend on api.gpm.read.'
    );
}

echo "P6_ADMIN2_WIRING_CONTRACT=PASS\n";
