<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

final class ReportRequestBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(
        ReportDefinition $definition,
        DateRange $dateRange
    ): array {
        return [
            '_reportId' => $definition->id(),
            'dateRanges' => [
                $dateRange->toApiArray(),
            ],
            'dimensions' => array_map(
                static fn (string $name): array => [
                    'name' => $name,
                ],
                $definition->dimensions()
            ),
            'metrics' => array_map(
                static fn (string $name): array => [
                    'name' => $name,
                ],
                $definition->metrics()
            ),
            'limit' => $definition->limit(),
        ];
    }
}
