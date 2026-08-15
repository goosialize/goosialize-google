<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class MetricComparison
{
    public function __construct(
        private float $current,
        private float $previous,
        private ?float $percentageChange
    ) {
    }

    public function current(): float
    {
        return $this->current;
    }

    public function previous(): float
    {
        return $this->previous;
    }

    public function percentageChange(): ?float
    {
        return $this->percentageChange;
    }
}
