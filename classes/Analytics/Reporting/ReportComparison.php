<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class ReportComparison
{
    /**
     * @param array<string, MetricComparison> $metrics
     */
    public function __construct(
        private ReportExecution $current,
        private ReportExecution $previous,
        private array $metrics
    ) {
    }

    public function current(): ReportExecution
    {
        return $this->current;
    }

    public function previous(): ReportExecution
    {
        return $this->previous;
    }

    /**
     * @return array<string, MetricComparison>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }
}
