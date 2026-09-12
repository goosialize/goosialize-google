<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$plugin =
    file_get_contents(
        $root
        . '/goosialize-google.php'
    );

$controller =
    file_get_contents(
        $root
        . '/classes/Admin/SearchConsoleDashboardController.php'
    );

if (
    $plugin === false
    || $controller === false
) {
    throw new RuntimeException(
        'Unable to read sitemap endpoint sources.'
    );
}

$checks = [
    'route' => [
        $plugin,
        '/goosialize-google/search-console/sitemap',
    ],

    'controller action' => [
        $controller,
        'public function sitemap(',
    ],

    'same permission surface' => [
        $controller,
        'api.goosialize_google.search_console.read',
    ],

    'SEO sitemap route consumption' => [
        $controller,
        'plugins.goosialize-seo.sitemap.route',
    ],

    'SEO canonical base consumption' => [
        $controller,
        'plugins.goosialize-seo.canonical.base_url',
    ],

    'forwarded HTTPS authority' => [
        $controller,
        'X-Forwarded-Proto',
    ],

    'status service' => [
        $controller,
        'CanonicalSitemapStatusService',
    ],
];

foreach (
    $checks
    as $name => [$source, $needle]
) {
    if (!str_contains(
        $source,
        $needle
    )) {
        throw new RuntimeException(
            'Missing sitemap endpoint contract: '
            . $name
        );
    }

    echo 'PASS '
        . $name
        . PHP_EOL;
}

echo "SEARCH_CONSOLE_SITEMAP_ENDPOINT_CONTRACT=PASS\n";
