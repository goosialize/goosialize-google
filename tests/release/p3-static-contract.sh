#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
    classes/Analytics/Reporting/DateRange.php
    classes/Analytics/Reporting/DateRangeFactory.php
    classes/Analytics/Reporting/ReportDefinition.php
    classes/Analytics/Reporting/ReportCatalog.php
    classes/Analytics/Reporting/ReportRequestBuilder.php
    classes/Analytics/Reporting/ReportRow.php
    classes/Analytics/Reporting/ReportResult.php
    classes/Analytics/Reporting/ReportResponseNormalizer.php
    classes/Analytics/Reporting/MetricComparison.php
    classes/Analytics/Reporting/ComparisonEngine.php
    classes/Analytics/Reporting/AnalyticsMetadata.php
    classes/Analytics/Reporting/MetadataNormalizer.php
    classes/Analytics/Reporting/IncompatibleReportException.php
    classes/Analytics/Reporting/ReportCompatibilityValidator.php
    classes/Analytics/Reporting/ReportExecution.php
    classes/Analytics/Reporting/ReportComparison.php
    classes/Analytics/Reporting/AnalyticsReportingService.php
    classes/Analytics/Testing/FakeAnalyticsDataClient.php
    tests/p3/report-definition-contract.php
    tests/p3/response-engine-contract.php
    tests/p3/reporting-service-contract.php
)

for file in "${required[@]}"; do
    test -f "$file"
done

grep -Fq "'overview'," \
    classes/Analytics/Reporting/ReportCatalog.php

grep -Fq "'activeUsers'" \
    classes/Analytics/Reporting/ReportCatalog.php

grep -Fq "'sessionDefaultChannelGroup'" \
    classes/Analytics/Reporting/ReportCatalog.php

grep -Fq "previousPeriod" \
    classes/Analytics/Reporting/DateRangeFactory.php

grep -Fq "_reportId" \
    classes/Analytics/Reporting/ReportRequestBuilder.php

grep -Fq "registerMetadata" \
    classes/Analytics/Testing/FakeAnalyticsDataClient.php

grep -Fq "ReportCompatibilityValidator" \
    classes/Analytics/Reporting/AnalyticsReportingService.php

echo "P3_STATIC_CONTRACT=PASS"
