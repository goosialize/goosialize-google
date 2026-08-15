<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final class ReportCompatibilityValidator
{
    public function validate(
        ReportDefinition $definition,
        AnalyticsMetadata $metadata
    ): void {
        $missing = [];

        foreach (
            $definition->dimensions()
            as $dimension
        ) {
            if (
                !$metadata->supportsDimension(
                    $dimension
                )
            ) {
                $missing[] =
                    'dimension:' . $dimension;
            }
        }

        foreach (
            $definition->metrics()
            as $metric
        ) {
            if (
                !$metadata->supportsMetric(
                    $metric
                )
            ) {
                $missing[] =
                    'metric:' . $metric;
            }
        }

        if ($missing !== []) {
            throw new IncompatibleReportException(
                'Unsupported GA4 fields: '
                . implode(', ', $missing)
            );
        }
    }
}
