<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use Goosialize\Google\Analytics\Connection\AnalyticsDataClientInterface;
use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use RuntimeException;

final class AnalyticsReportingService
{
    public function __construct(
        private AnalyticsDataClientInterface $client,
        private ReportCatalog $catalog,
        private ReportRequestBuilder $requestBuilder,
        private ReportResponseNormalizer $normalizer,
        private MetadataNormalizer $metadataNormalizer,
        private ReportCompatibilityValidator $compatibilityValidator,
        private DateRangeFactory $dateRangeFactory,
        private ComparisonEngine $comparisonEngine
    ) {
    }

    public function run(
        AnalyticsPropertyId $propertyId,
        string $reportId,
        DateRange $dateRange
    ): ReportExecution {
        $definition = $this->catalog->get(
            $reportId
        );

        $metadata = $this->metadataNormalizer
            ->normalize(
                $this->client->getMetadata(
                    $propertyId
                )
            );

        $this->compatibilityValidator
            ->validate(
                $definition,
                $metadata
            );

        $request = $this->requestBuilder
            ->build(
                $definition,
                $dateRange
            );

        $response = $this->client
            ->runReport(
                $propertyId,
                $request
            );

        return new ReportExecution(
            $definition->id(),
            $dateRange,
            $this->normalizer->normalize(
                $response
            )
        );
    }

    public function compare(
        AnalyticsPropertyId $propertyId,
        string $reportId,
        DateRange $currentRange
    ): ReportComparison {
        $current = $this->run(
            $propertyId,
            $reportId,
            $currentRange
        );

        $previous = $this->run(
            $propertyId,
            $reportId,
            $this->dateRangeFactory
                ->previousPeriod(
                    $currentRange
                )
        );

        $currentRows = $current
            ->result()
            ->rows();

        $previousRows = $previous
            ->result()
            ->rows();

        if (
            count($currentRows) !== 1
            || count($previousRows) !== 1
        ) {
            throw new RuntimeException(
                'Metric comparison requires single-row reports.'
            );
        }

        $currentMetrics =
            $currentRows[0]->metrics();

        $previousMetrics =
            $previousRows[0]->metrics();

        $comparisons = [];

        foreach (
            $currentMetrics
            as $metric => $currentValue
        ) {
            $previousValue =
                $previousMetrics[$metric]
                ?? 0;

            if (
                !is_int($currentValue)
                && !is_float($currentValue)
            ) {
                continue;
            }

            if (
                !is_int($previousValue)
                && !is_float($previousValue)
            ) {
                continue;
            }

            $comparisons[$metric] =
                $this->comparisonEngine
                    ->compare(
                        $currentValue,
                        $previousValue
                    );
        }

        return new ReportComparison(
            $current,
            $previous,
            $comparisons
        );
    }
}
