<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Testing;

use Goosialize\Google\Analytics\Connection\AnalyticsDataClientInterface;
use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use RuntimeException;

final class FakeAnalyticsDataClient implements AnalyticsDataClientInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $responses = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $metadata = [];

    /**
     * @param array<string, mixed> $response
     */
    public function registerReportResponse(
        string $propertyId,
        string $reportId,
        array $response
    ): void {
        $this->responses[
            $propertyId . ':' . $reportId
        ] = $response;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function registerMetadata(
        string $propertyId,
        array $metadata
    ): void {
        $this->metadata[$propertyId] = $metadata;
    }

    public function runReport(
        AnalyticsPropertyId $propertyId,
        array $report
    ): array {
        $reportId = $report['_reportId']
            ?? null;

        if (
            !is_string($reportId)
            || $reportId === ''
        ) {
            throw new RuntimeException(
                'Fake client requires _reportId.'
            );
        }

        $key = $propertyId->value()
            . ':'
            . $reportId;

        if (
            !array_key_exists(
                $key,
                $this->responses
            )
        ) {
            throw new RuntimeException(
                'No fake response registered for '
                . $key
            );
        }

        return $this->responses[$key];
    }

    public function getMetadata(
        AnalyticsPropertyId $propertyId
    ): array {
        return $this->metadata[
            $propertyId->value()
        ] ?? [
            'property' => $propertyId->resourceName(),
            'dimensions' => [],
            'metrics' => [],
        ];
    }
}
