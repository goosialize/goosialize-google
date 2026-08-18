<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

use InvalidArgumentException;

final readonly class SearchConsoleQuery
{
    /**
     * @param list<SearchConsoleDimension> $dimensions
     */
    public function __construct(
        public SearchConsoleDateRange $dateRange,
        public array $dimensions = [],
        public int $rowLimit = 1000,
        public int $startRow = 0,
    ) {
        if ($this->rowLimit < 1 || $this->rowLimit > 25000) {
            throw new InvalidArgumentException(
                'Search Console rowLimit must be between 1 and 25000.'
            );
        }

        if ($this->startRow < 0) {
            throw new InvalidArgumentException(
                'Search Console startRow must not be negative.'
            );
        }

        foreach ($this->dimensions as $dimension) {
            if (!$dimension instanceof SearchConsoleDimension) {
                throw new InvalidArgumentException(
                    'Search Console dimensions must use SearchConsoleDimension values.'
                );
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return [
            ...$this->dateRange->toApiPayload(),
            'dimensions' => array_map(
                static fn (
                    SearchConsoleDimension $dimension
                ): string => $dimension->value,
                $this->dimensions
            ),
            'rowLimit' => $this->rowLimit,
            'startRow' => $this->startRow,
        ];
    }
}
