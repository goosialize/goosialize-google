<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use RuntimeException;

final class ReportResponseNormalizer
{
    /**
     * @param array<string, mixed> $response
     */
    public function normalize(array $response): ReportResult
    {
        $dimensionNames = [];

        foreach (
            $response['dimensionHeaders'] ?? []
            as $header
        ) {
            $name = $header['name'] ?? null;

            if (!is_string($name) || $name === '') {
                throw new RuntimeException(
                    'Invalid dimension header.'
                );
            }

            $dimensionNames[] = $name;
        }

        $metricNames = [];

        foreach (
            $response['metricHeaders'] ?? []
            as $header
        ) {
            $name = $header['name'] ?? null;

            if (!is_string($name) || $name === '') {
                throw new RuntimeException(
                    'Invalid metric header.'
                );
            }

            $metricNames[] = $name;
        }

        $rows = [];

        foreach ($response['rows'] ?? [] as $rawRow) {
            $dimensions = [];

            foreach (
                $dimensionNames as $index => $name
            ) {
                $value = $rawRow['dimensionValues'][$index]['value']
                    ?? '';

                $dimensions[$name] = (string) $value;
            }

            $metrics = [];

            foreach (
                $metricNames as $index => $name
            ) {
                $value = $rawRow['metricValues'][$index]['value']
                    ?? '0';

                $metrics[$name] = $this->normalizeMetric(
                    (string) $value
                );
            }

            $rows[] = new ReportRow(
                $dimensions,
                $metrics
            );
        }

        $rowCount = $response['rowCount']
            ?? count($rows);

        if (!is_int($rowCount)) {
            $rowCount = (int) $rowCount;
        }

        return new ReportResult(
            $dimensionNames,
            $metricNames,
            $rows,
            $rowCount
        );
    }

    private function normalizeMetric(
        string $value
    ): float|int|string {
        if (preg_match('/^-?[0-9]+$/', $value)) {
            return (int) $value;
        }

        if (
            preg_match(
                '/^-?[0-9]+\.[0-9]+$/',
                $value
            )
        ) {
            return (float) $value;
        }

        return $value;
    }
}
