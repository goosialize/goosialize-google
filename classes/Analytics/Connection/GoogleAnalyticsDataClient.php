<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\GetMetadataRequest;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Throwable;

final class GoogleAnalyticsDataClient
    implements AnalyticsDataClientInterface
{
    public function __construct(
        private BetaAnalyticsDataClient $client
    ) {
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    public function runReport(
        AnalyticsPropertyId $propertyId,
        array $report
    ): array {
        try {
            $request = new RunReportRequest();

            $request->setProperty(
                $propertyId->resourceName()
            );

            $dateRanges = [];

            foreach (
                $report['dateRanges'] ?? []
                as $range
            ) {
                if (!is_array($range)) {
                    continue;
                }

                $dateRanges[] = new DateRange([
                    'start_date' =>
                        (string) (
                            $range['startDate']
                            ?? ''
                        ),
                    'end_date' =>
                        (string) (
                            $range['endDate']
                            ?? ''
                        ),
                ]);
            }

            $request->setDateRanges(
                $dateRanges
            );

            $dimensions = [];

            foreach (
                $report['dimensions'] ?? []
                as $dimension
            ) {
                if (!is_array($dimension)) {
                    continue;
                }

                $dimensions[] = new Dimension([
                    'name' =>
                        (string) (
                            $dimension['name']
                            ?? ''
                        ),
                ]);
            }

            $request->setDimensions(
                $dimensions
            );

            $metrics = [];

            foreach (
                $report['metrics'] ?? []
                as $metric
            ) {
                if (!is_array($metric)) {
                    continue;
                }

                $metrics[] = new Metric([
                    'name' =>
                        (string) (
                            $metric['name']
                            ?? ''
                        ),
                ]);
            }

            $request->setMetrics(
                $metrics
            );

            if (
                isset($report['limit'])
                && is_int($report['limit'])
            ) {
                $request->setLimit(
                    $report['limit']
                );
            }

            $response =
                $this->client->runReport(
                    $request
                );

            return json_decode(
                $response->serializeToJsonString(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new AnalyticsApiException(
                'Google Analytics Data API request failed.',
                0,
                $e
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(
        AnalyticsPropertyId $propertyId
    ): array {
        try {
            $request = new GetMetadataRequest();

            $request->setName(
                $propertyId->resourceName()
                . '/metadata'
            );

            $response =
                $this->client->getMetadata(
                    $request
                );

            return json_decode(
                $response->serializeToJsonString(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new AnalyticsApiException(
                'Google Analytics metadata request failed.',
                0,
                $e
            );
        }
    }
}
