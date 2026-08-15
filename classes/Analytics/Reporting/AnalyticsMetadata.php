<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final readonly class AnalyticsMetadata
{
    /**
     * @param list<string> $dimensions
     * @param list<string> $metrics
     */
    public function __construct(
        private array $dimensions,
        private array $metrics
    ) {
    }

    /**
     * @return list<string>
     */
    public function dimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @return list<string>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }

    public function supportsDimension(
        string $name
    ): bool {
        return in_array(
            $name,
            $this->dimensions,
            true
        );
    }

    public function supportsMetric(
        string $name
    ): bool {
        return in_array(
            $name,
            $this->metrics,
            true
        );
    }
}
