<?php

declare(strict_types=1);

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

use Goosialize\Google\Analytics\Reporting\DateRangeFactory;
use Goosialize\Google\Analytics\Reporting\ReportCatalog;
use Goosialize\Google\Analytics\Reporting\ReportRequestBuilder;

$end = new DateTimeImmutable(
    '2026-08-15'
);

$factory = new DateRangeFactory();

$current = $factory->trailingDays(
    $end,
    7
);

$previous = $factory->previousPeriod(
    $current
);

if (
    $current->toApiArray() !== [
        'startDate' => '2026-08-09',
        'endDate' => '2026-08-15',
    ]
) {
    throw new RuntimeException(
        'Current 7-day range failed.'
    );
}

if (
    $previous->toApiArray() !== [
        'startDate' => '2026-08-02',
        'endDate' => '2026-08-08',
    ]
) {
    throw new RuntimeException(
        'Previous period failed.'
    );
}

$catalog = new ReportCatalog();

$overview = $catalog->get(
    'overview'
);

if (
    !in_array(
        'activeUsers',
        $overview->metrics(),
        true
    )
) {
    throw new RuntimeException(
        'Overview activeUsers metric missing.'
    );
}

$builder = new ReportRequestBuilder();

$request = $builder->build(
    $catalog->get('traffic_channels'),
    $current
);

if (
    ($request['dateRanges'][0]['startDate'] ?? null)
    !== '2026-08-09'
) {
    throw new RuntimeException(
        'Request date range failed.'
    );
}

if (
    ($request['dimensions'][0]['name'] ?? null)
    !== 'sessionDefaultChannelGroup'
) {
    throw new RuntimeException(
        'Traffic dimension failed.'
    );
}

if (
    ($request['metrics'][0]['name'] ?? null)
    !== 'sessions'
) {
    throw new RuntimeException(
        'Traffic metric failed.'
    );
}

echo "P3_REPORT_DEFINITION_CONTRACT=PASS\n";
