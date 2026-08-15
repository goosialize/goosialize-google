<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$requires = [
    'classes/Analytics/Connection/AnalyticsPropertyId.php',
    'classes/Analytics/Connection/AnalyticsDataClientInterface.php',

    'classes/Analytics/Discovery/AnalyticsPropertySummary.php',
    'classes/Analytics/Discovery/AnalyticsPropertyDiscoveryInterface.php',

    'classes/Analytics/Reporting/DateRange.php',
    'classes/Analytics/Reporting/DateRangeFactory.php',
    'classes/Analytics/Reporting/ReportDefinition.php',
    'classes/Analytics/Reporting/ReportCatalog.php',
    'classes/Analytics/Reporting/ReportRequestBuilder.php',
    'classes/Analytics/Reporting/ReportRow.php',
    'classes/Analytics/Reporting/ReportResult.php',
    'classes/Analytics/Reporting/ReportResponseNormalizer.php',
    'classes/Analytics/Reporting/MetricComparison.php',
    'classes/Analytics/Reporting/ComparisonEngine.php',
    'classes/Analytics/Reporting/AnalyticsMetadata.php',
    'classes/Analytics/Reporting/MetadataNormalizer.php',
    'classes/Analytics/Reporting/IncompatibleReportException.php',
    'classes/Analytics/Reporting/ReportCompatibilityValidator.php',
    'classes/Analytics/Reporting/ReportExecution.php',
    'classes/Analytics/Reporting/ReportComparison.php',
    'classes/Analytics/Reporting/AnalyticsReportingService.php',

    'classes/Analytics/Testing/FakeAnalyticsDataClient.php',
    'classes/Analytics/Testing/FakeAnalyticsPropertyDiscovery.php',

    'classes/Analytics/Dashboard/DashboardPeriod.php',
    'classes/Analytics/Dashboard/AnalyticsPropertyDirectory.php',
    'classes/Analytics/Dashboard/AnalyticsDashboardService.php',
];

foreach ($requires as $relative) {
    require_once $root . '/' . $relative;
}

use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Analytics\Dashboard\AnalyticsDashboardService;
use Goosialize\Google\Analytics\Dashboard\AnalyticsPropertyDirectory;
use Goosialize\Google\Analytics\Dashboard\DashboardPeriod;
use Goosialize\Google\Analytics\Discovery\AnalyticsPropertySummary;
use Goosialize\Google\Analytics\Reporting\AnalyticsReportingService;
use Goosialize\Google\Analytics\Reporting\ComparisonEngine;
use Goosialize\Google\Analytics\Reporting\DateRangeFactory;
use Goosialize\Google\Analytics\Reporting\MetadataNormalizer;
use Goosialize\Google\Analytics\Reporting\ReportCatalog;
use Goosialize\Google\Analytics\Reporting\ReportCompatibilityValidator;
use Goosialize\Google\Analytics\Reporting\ReportRequestBuilder;
use Goosialize\Google\Analytics\Reporting\ReportResponseNormalizer;
use Goosialize\Google\Analytics\Testing\FakeAnalyticsDataClient;
use Goosialize\Google\Analytics\Testing\FakeAnalyticsPropertyDiscovery;

$property =
    new AnalyticsPropertySummary(
        'accounts/345449079',
        'Goosialize Ltd',
        new AnalyticsPropertyId(
            '477785730'
        ),
        'Agency'
    );

$discovery =
    new FakeAnalyticsPropertyDiscovery([
        $property,
    ]);

$directory =
    new AnalyticsPropertyDirectory(
        $discovery
    );

$directoryPayload =
    $directory->payload();

if (
    ($directoryPayload['data'][0]['property_id'] ?? null)
    !== '477785730'
) {
    throw new RuntimeException(
        'Property directory contract failed.'
    );
}

$client = new FakeAnalyticsDataClient();

$dimensions = [
    'pagePath',
    'pageTitle',
    'sessionDefaultChannelGroup',
    'deviceCategory',
    'country',
    'eventName',
];

$metrics = [
    'activeUsers',
    'newUsers',
    'sessions',
    'screenPageViews',
    'engagementRate',
    'averageSessionDuration',
    'eventCount',
    'keyEvents',
    'totalUsers',
];

$client->registerMetadata(
    '477785730',
    [
        'dimensions' => array_map(
            static fn (string $name): array =>
                ['apiName' => $name],
            $dimensions
        ),
        'metrics' => array_map(
            static fn (string $name): array =>
                ['apiName' => $name],
            $metrics
        ),
    ]
);

$overviewMetrics = [
    'activeUsers',
    'newUsers',
    'sessions',
    'screenPageViews',
    'engagementRate',
    'averageSessionDuration',
    'eventCount',
    'keyEvents',
];

$client->registerReportResponse(
    '477785730',
    'overview',
    [
        'metricHeaders' => array_map(
            static fn (string $name): array =>
                ['name' => $name],
            $overviewMetrics
        ),
        'rows' => [
            [
                'metricValues' => [
                    ['value' => '120'],
                    ['value' => '80'],
                    ['value' => '180'],
                    ['value' => '450'],
                    ['value' => '0.65'],
                    ['value' => '90.5'],
                    ['value' => '720'],
                    ['value' => '12'],
                ],
            ],
        ],
        'rowCount' => 1,
    ]
);

$emptyReports = [
    'top_pages' => [
        ['pagePath', 'pageTitle'],
        [
            'screenPageViews',
            'activeUsers',
            'averageSessionDuration',
        ],
    ],
    'traffic_channels' => [
        ['sessionDefaultChannelGroup'],
        [
            'sessions',
            'activeUsers',
            'engagementRate',
            'keyEvents',
        ],
    ],
    'devices' => [
        ['deviceCategory'],
        [
            'sessions',
            'activeUsers',
        ],
    ],
    'countries' => [
        ['country'],
        [
            'activeUsers',
            'sessions',
        ],
    ],
    'events' => [
        ['eventName'],
        [
            'eventCount',
            'totalUsers',
        ],
    ],
];

foreach (
    $emptyReports
    as $id => [$reportDimensions, $reportMetrics]
) {
    $client->registerReportResponse(
        '477785730',
        $id,
        [
            'dimensionHeaders' =>
                array_map(
                    static fn (string $name): array =>
                        ['name' => $name],
                    $reportDimensions
                ),
            'metricHeaders' =>
                array_map(
                    static fn (string $name): array =>
                        ['name' => $name],
                    $reportMetrics
                ),
            'rows' => [],
            'rowCount' => 0,
        ]
    );
}

$dateRanges =
    new DateRangeFactory();

$reporting =
    new AnalyticsReportingService(
        $client,
        new ReportCatalog(),
        new ReportRequestBuilder(),
        new ReportResponseNormalizer(),
        new MetadataNormalizer(),
        new ReportCompatibilityValidator(),
        $dateRanges,
        new ComparisonEngine()
    );

$dashboard =
    new AnalyticsDashboardService(
        $discovery,
        $reporting,
        $dateRanges
    );

$payload =
    $dashboard->dashboard(
        '477785730',
        DashboardPeriod::DAYS_30,
        new DateTimeImmutable(
            '2026-08-15'
        )
    );

if (
    ($payload['overview']['activeUsers'] ?? null)
    !== 120
) {
    throw new RuntimeException(
        'Dashboard KPI contract failed.'
    );
}

if (
    ($payload['period']['days'] ?? null)
    !== 30
) {
    throw new RuntimeException(
        'Dashboard period contract failed.'
    );
}

if (
    ($payload['period']['start_date'] ?? null)
    !== '2026-07-17'
) {
    throw new RuntimeException(
        'Dashboard date range contract failed.'
    );
}

if (($payload['empty'] ?? null) !== false) {
    throw new RuntimeException(
        'Dashboard empty-state contract failed.'
    );
}

try {
    $dashboard->dashboard(
        '999999999',
        DashboardPeriod::DAYS_7,
        new DateTimeImmutable(
            '2026-08-15'
        )
    );

    throw new RuntimeException(
        'Inaccessible property was accepted.'
    );
} catch (RuntimeException $e) {
    if (
        $e->getMessage()
        === 'Inaccessible property was accepted.'
    ) {
        throw $e;
    }
}

echo "P6_DASHBOARD_BACKEND_CONTRACT=PASS\n";
