<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

final class SearchConsoleResponseNormalizer
{
    /**
     * @param array<string, mixed> $response
     *
     * @return list<SearchConsoleRow>
     */
    public function normalize(array $response): array
    {
        $rows = $response['rows'] ?? [];

        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $keys = $row['keys'] ?? [];

            if (!is_array($keys)) {
                $keys = [];
            }

            $normalized[] = new SearchConsoleRow(
                array_values(
                    array_map(
                        'strval',
                        $keys
                    )
                ),
                (float) ($row['clicks'] ?? 0.0),
                (float) ($row['impressions'] ?? 0.0),
                (float) ($row['ctr'] ?? 0.0),
                (float) ($row['position'] ?? 0.0),
            );
        }

        return $normalized;
    }
}
