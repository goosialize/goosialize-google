<?php

declare(strict_types=1);

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsPropertyId.php';

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsDataClientInterface.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/DateRange.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/DateRangeFactory.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportDefinition.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportCatalog.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportRequestBuilder.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportRow.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportResult.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportResponseNormalizer.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/MetricComparison.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ComparisonEngine.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/AnalyticsMetadata.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/MetadataNormalizer.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/IncompatibleReportException.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportCompatibilityValidator.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportExecution.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/ReportComparison.php';

require __DIR__
    . '/../../classes/Analytics/Reporting/AnalyticsReportingService.php';

require __DIR__
    . '/../../classes/Analytics/Testing/FakeAnalyticsDataClient.php';

use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Analytics\Reporting\AnalyticsReportingService;
use Goosialize\Google\Analytics\Reporting\ComparisonEngine;
use Goosialize\Google\Analytics\Reporting\DateRangeFactory;
use Goosialize\Google\Analytics\Reporting\MetadataNormalizer;
use Goosialize\Google\Analytics\Reporting\ReportCatalog;
use Goosialize\Google\Analytics\Reporting\ReportCompatibilityValidator;
use Goosialize\Google\Analytics\Reporting\ReportRequestBuilder;
use Goosialize\Google\Analytics\Reporting\ReportResponseNormalizer;
use Goosialize\Google\Analytics\Testing\FakeAnalyticsDataClient;

$propertyId = new AnalyticsPropertyId(
    '123456789'
);

$client = new FakeAnalyticsDataClient();

$client->registerMetadata(
    '123456789',
    [
        'dimensions' => [
            ['apiName' => 'pagePath'],
            ['apiName' => 'pageTitle'],
            ['apiName' => 'sessionDefaultChannelGroup'],
            ['apiName' => 'deviceCategory'],
            ['apiName' => 'country'],
            ['apiName' => 'eventName'],
        ],
        'metrics' => [
            ['apiName' => 'activeUsers'],
            ['apiName' => 'newUsers'],
            ['apiName' => 'sessions'],
            ['apiName' => 'screenPageViews'],
            ['apiName' => 'engagementRate'],
            ['apiName' => 'averageSessionDuration'],
            ['apiName' => 'eventCount'],
            ['apiName' => 'keyEvents'],
            ['apiName' => 'totalUsers'],
        ],
    ]
);

$client->registerReportResponse(
    '123456789',
    'overview',
    [
        'metricHeaders' => [
            ['name' => 'activeUsers'],
            ['name' => 'newUsers'],
            ['name' => 'sessions'],
            ['name' => 'screenPageViews'],
            ['name' => 'engagementRate'],
            ['name' => 'averageSessionDuration'],
            ['name' => 'eventCount'],
            ['name' => 'keyEvents'],
        ],
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

$factory = new DateRangeFactory();

$service = new AnalyticsReportingService(
    $client,
    new ReportCatalog(),
    new ReportRequestBuilder(),
    new ReportResponseNormalizer(),
    new MetadataNormalizer(),
    new ReportCompatibilityValidator(),
    $factory,
    new ComparisonEngine()
);

$range = $factory->trailingDays(
    new DateTimeImmutable(
        '2026-08-15'
    ),
    7
);

$result = $service->run(
    $propertyId,
    'overview',
    $range
);

if (
    $result->reportId()
    !== 'overview'
) {
    throw new RuntimeException(
        'Report execution ID failed.'
    );
}

if (
    $result
        ->result()
        ->rows()[0]
        ->metrics()['sessions']
    !== 180
) {
    throw new RuntimeException(
        'Reporting service metric failed.'
    );
}

echo "P3_REPORTING_SERVICE_CONTRACT=PASS\n";
