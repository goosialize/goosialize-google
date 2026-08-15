<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final class MetadataNormalizer
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function normalize(
        array $metadata
    ): AnalyticsMetadata {
        $dimensions = [];

        foreach (
            $metadata['dimensions'] ?? []
            as $item
        ) {
            if (is_string($item)) {
                $name = trim($item);
            } elseif (is_array($item)) {
                $name = trim(
                    (string) (
                        $item['apiName']
                        ?? $item['name']
                        ?? ''
                    )
                );
            } else {
                continue;
            }

            if ($name !== '') {
                $dimensions[] = $name;
            }
        }

        $metrics = [];

        foreach (
            $metadata['metrics'] ?? []
            as $item
        ) {
            if (is_string($item)) {
                $name = trim($item);
            } elseif (is_array($item)) {
                $name = trim(
                    (string) (
                        $item['apiName']
                        ?? $item['name']
                        ?? ''
                    )
                );
            } else {
                continue;
            }

            if ($name !== '') {
                $metrics[] = $name;
            }
        }

        return new AnalyticsMetadata(
            array_values(
                array_unique($dimensions)
            ),
            array_values(
                array_unique($metrics)
            )
        );
    }
}
