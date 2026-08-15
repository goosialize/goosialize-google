<?php

declare(strict_types=1);

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsPropertyId.php';

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsDataClientInterface.php';

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
    . '/../../classes/Analytics/Testing/FakeAnalyticsDataClient.php';

use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Analytics\Reporting\ComparisonEngine;
use Goosialize\Google\Analytics\Reporting\ReportResponseNormalizer;
use Goosialize\Google\Analytics\Testing\FakeAnalyticsDataClient;

$propertyId = new AnalyticsPropertyId(
    '123456789'
);

$client = new FakeAnalyticsDataClient();

$client->registerReportResponse(
    '123456789',
    'overview',
    [
        'dimensionHeaders' => [],
        'metricHeaders' => [
            ['name' => 'activeUsers'],
            ['name' => 'sessions'],
            ['name' => 'engagementRate'],
        ],
        'rows' => [
            [
                'dimensionValues' => [],
                'metricValues' => [
                    ['value' => '120'],
                    ['value' => '180'],
                    ['value' => '0.65'],
                ],
            ],
        ],
        'rowCount' => 1,
    ]
);

$response = $client->runReport(
    $propertyId,
    [
        '_reportId' => 'overview',
    ]
);

$normalizer = new ReportResponseNormalizer();

$result = $normalizer->normalize(
    $response
);

if ($result->rowCount() !== 1) {
    throw new RuntimeException(
        'Row count normalization failed.'
    );
}

$row = $result->rows()[0];

if (
    $row->metrics()['activeUsers']
    !== 120
) {
    throw new RuntimeException(
        'Integer metric normalization failed.'
    );
}

if (
    abs(
        $row->metrics()['engagementRate']
        - 0.65
    ) > 0.000001
) {
    throw new RuntimeException(
        'Float metric normalization failed.'
    );
}

$engine = new ComparisonEngine();

$comparison = $engine->compare(
    120,
    100
);

if (
    abs(
        ($comparison->percentageChange() ?? 0)
        - 20.0
    ) > 0.000001
) {
    throw new RuntimeException(
        'Percentage comparison failed.'
    );
}

$zeroBase = $engine->compare(
    10,
    0
);

if (
    $zeroBase->percentageChange()
    !== null
) {
    throw new RuntimeException(
        'Zero baseline comparison failed.'
    );
}

$bothZero = $engine->compare(
    0,
    0
);

if (
    $bothZero->percentageChange()
    !== 0.0
) {
    throw new RuntimeException(
        'Zero-to-zero comparison failed.'
    );
}

echo "P3_RESPONSE_ENGINE_CONTRACT=PASS\n";
