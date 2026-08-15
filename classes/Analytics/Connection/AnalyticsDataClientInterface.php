<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

interface AnalyticsDataClientInterface
{
    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    public function runReport(
        AnalyticsPropertyId $propertyId,
        array $report
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(
        AnalyticsPropertyId $propertyId
    ): array;
}
