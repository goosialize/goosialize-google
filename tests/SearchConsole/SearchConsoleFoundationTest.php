<?php

declare(strict_types=1);

use Goosialize\Google\SearchConsole\Connection\SearchConsoleProperty;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleDateRange;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleDimension;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleQuery;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleReportingService;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleResponseNormalizer;
use Goosialize\Google\SearchConsole\Testing\FakeSearchConsoleClient;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$assertions = 0;

$assert = static function (
    bool $condition,
    string $message
) use (&$assertions): void {
    ++$assertions;

    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$domain = new SearchConsoleProperty(
    'sc-domain:example.com',
    'siteOwner'
);

$assert(
    $domain->isDomainProperty(),
    'Domain property detection failed.'
);

$urlPrefix = new SearchConsoleProperty(
    'https://www.example.com/',
    'siteFullUser'
);

$assert(
    !$urlPrefix->isDomainProperty(),
    'URL-prefix property detection failed.'
);

$range = new SearchConsoleDateRange(
    new DateTimeImmutable('2026-08-01'),
    new DateTimeImmutable('2026-08-07')
);

$query = new SearchConsoleQuery(
    $range,
    [
        SearchConsoleDimension::Query,
        SearchConsoleDimension::Page,
    ],
    500,
    0
);

$payload = $query->toApiPayload();

$assert(
    $payload === [
        'startDate' => '2026-08-01',
        'endDate' => '2026-08-07',
        'dimensions' => [
            'query',
            'page',
        ],
        'rowLimit' => 500,
        'startRow' => 0,
    ],
    'Query payload contract failed.'
);

$client = new FakeSearchConsoleClient(
    [
        [
            'siteUrl' => 'sc-domain:example.com',
            'permissionLevel' => 'siteOwner',
        ],
    ],
    [
        'rows' => [
            [
                'keys' => [
                    'grav cms',
                    'https://www.example.com/grav',
                ],
                'clicks' => 12,
                'impressions' => 120,
                'ctr' => 0.10,
                'position' => 3.5,
            ],
        ],
    ]
);

$service = new SearchConsoleReportingService(
    $client,
    new SearchConsoleResponseNormalizer()
);

$properties = $service->listProperties();

$assert(
    count($properties) === 1,
    'Property discovery count failed.'
);

$assert(
    $properties[0]->siteUrl === 'sc-domain:example.com',
    'Property siteUrl normalization failed.'
);

$result = $service->execute(
    $properties[0],
    $query
);

$assert(
    count($result->rows) === 1,
    'Search Analytics result row count failed.'
);

$assert(
    $result->rows[0]->keys === [
        'grav cms',
        'https://www.example.com/grav',
    ],
    'Search Analytics row keys failed.'
);

$assert(
    $result->rows[0]->clicks === 12.0,
    'Clicks normalization failed.'
);

$assert(
    $result->rows[0]->impressions === 120.0,
    'Impressions normalization failed.'
);

$assert(
    $result->rows[0]->ctr === 0.10,
    'CTR normalization failed.'
);

$assert(
    $result->rows[0]->position === 3.5,
    'Position normalization failed.'
);

$invalidLimitRejected = false;

try {
    new SearchConsoleQuery(
        $range,
        [],
        25001
    );
} catch (InvalidArgumentException) {
    $invalidLimitRejected = true;
}

$assert(
    $invalidLimitRejected,
    'Invalid rowLimit was not rejected.'
);

$invalidRangeRejected = false;

try {
    new SearchConsoleDateRange(
        new DateTimeImmutable('2026-08-08'),
        new DateTimeImmutable('2026-08-07')
    );
} catch (InvalidArgumentException) {
    $invalidRangeRejected = true;
}

$assert(
    $invalidRangeRejected,
    'Invalid date range was not rejected.'
);

echo sprintf(
    "SEARCH_CONSOLE_FOUNDATION_ASSERTIONS=%d\n",
    $assertions
);

echo "SEARCH_CONSOLE_FOUNDATION=PASS\n";
