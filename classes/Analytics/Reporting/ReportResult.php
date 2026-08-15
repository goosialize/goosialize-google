<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class ReportResult
{
    /**
     * @param list<string> $dimensionNames
     * @param list<string> $metricNames
     * @param list<ReportRow> $rows
     */
    public function __construct(
        private array $dimensionNames,
        private array $metricNames,
        private array $rows,
        private int $rowCount
    ) {
    }

    /**
     * @return list<string>
     */
    public function dimensionNames(): array
    {
        return $this->dimensionNames;
    }

    /**
     * @return list<string>
     */
    public function metricNames(): array
    {
        return $this->metricNames;
    }

    /**
     * @return list<ReportRow>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }
}
