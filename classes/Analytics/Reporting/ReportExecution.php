<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class ReportExecution
{
    public function __construct(
        private string $reportId,
        private DateRange $dateRange,
        private ReportResult $result
    ) {
    }

    public function reportId(): string
    {
        return $this->reportId;
    }

    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }

    public function result(): ReportResult
    {
        return $this->result;
    }
}
