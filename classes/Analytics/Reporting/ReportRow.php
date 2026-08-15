<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class ReportRow
{
    /**
     * @param array<string, string> $dimensions
     * @param array<string, float|int|string> $metrics
     */
    public function __construct(
        private array $dimensions,
        private array $metrics
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function dimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @return array<string, float|int|string>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }
}
