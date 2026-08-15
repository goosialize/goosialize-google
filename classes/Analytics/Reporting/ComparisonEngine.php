<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final class ComparisonEngine
{
    public function compare(
        float|int $current,
        float|int $previous
    ): MetricComparison {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0.0) {
            return new MetricComparison(
                $current,
                $previous,
                $current == 0.0 ? 0.0 : null
            );
        }

        $change = (
            ($current - $previous)
            / abs($previous)
        ) * 100;

        return new MetricComparison(
            $current,
            $previous,
            $change
        );
    }
}
