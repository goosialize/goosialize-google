<?php

declare(strict_types=1);

use Goosialize\Google\SearchConsole\Dashboard\SearchConsoleDashboardPeriod;
use Goosialize\Google\SearchConsole\Dashboard\SearchConsoleDashboardService;
use Goosialize\Google\SearchConsole\Dashboard\SearchConsolePropertyDirectory;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleReportingService;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleResponseNormalizer;
use Goosialize\Google\SearchConsole\Testing\FakeSearchConsoleClient;

require dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$assertions = 0;

$assert = static function (
    bool $condition,
    string $message
) use (&$assertions): void {
    ++$assertions;

    if (!$condition) {
        throw new RuntimeException(
            $message
        );
    }
};

$client =
    new FakeSearchConsoleClient(
        [
            [
                'siteUrl' =>
                    'sc-domain:example.com',
                'permissionLevel' =>
                    'siteOwner',
            ],
        ],
        [
            'rows' => [
                [
                    'keys' => [
                        'example',
                    ],
                    'clicks' => 100,
                    'impressions' => 1000,
                    'ctr' => 0.1,
                    'position' => 3.25,
                ],
            ],
        ]
    );

$reporting =
    new SearchConsoleReportingService(
        $client,
        new SearchConsoleResponseNormalizer()
    );

$directory =
    (
        new SearchConsolePropertyDirectory(
            $reporting
        )
    )->payload();

$assert(
    count($directory['data']) === 1,
    'Search Console property directory count failed.'
);

$assert(
    $directory['data'][0]['site_url']
        === 'sc-domain:example.com',
    'Search Console property URL failed.'
);

$assert(
    $directory['data'][0]['property_type']
        === 'domain',
    'Search Console property type failed.'
);

$dashboard =
    (
        new SearchConsoleDashboardService(
            $reporting
        )
    )->dashboard(
        'sc-domain:example.com',
        SearchConsoleDashboardPeriod::DAYS_30,
        new DateTimeImmutable(
            '2026-08-18'
        )
    );

$assert(
    $dashboard['property']['site_url']
        === 'sc-domain:example.com',
    'Dashboard property failed.'
);

$assert(
    $dashboard['period']['days']
        === 30,
    'Dashboard period failed.'
);

$assert(
    $dashboard['period']['start_date']
        === '2026-07-20',
    'Dashboard start date failed.'
);

$assert(
    $dashboard['period']['end_date']
        === '2026-08-18',
    'Dashboard end date failed.'
);

$assert(
    $dashboard['overview']['clicks']
        === 100.0,
    'Dashboard clicks failed.'
);

$assert(
    $dashboard['overview']['impressions']
        === 1000.0,
    'Dashboard impressions failed.'
);

$assert(
    $dashboard['overview']['ctr']
        === 0.1,
    'Dashboard CTR failed.'
);

$assert(
    $dashboard['overview']['position']
        === 3.25,
    'Dashboard position failed.'
);

$assert(
    count($dashboard['top_queries'])
        === 1,
    'Dashboard top queries failed.'
);

$forbidden = false;

try {
    (
        new SearchConsoleDashboardService(
            $reporting
        )
    )->dashboard(
        'sc-domain:missing.example',
        SearchConsoleDashboardPeriod::DAYS_7,
        new DateTimeImmutable(
            '2026-08-18'
        )
    );
} catch (RuntimeException $e) {
    $forbidden =
        str_contains(
            $e->getMessage(),
            'not accessible'
        );
}

$assert(
    $forbidden,
    'Inaccessible Search Console property was accepted.'
);

echo sprintf(
    "SEARCH_CONSOLE_DASHBOARD_ASSERTIONS=%d\n",
    $assertions
);

echo "SEARCH_CONSOLE_DASHBOARD=PASS\n";
