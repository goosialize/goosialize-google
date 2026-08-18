<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

final readonly class SearchConsoleResult
{
    /**
     * @param list<SearchConsoleRow> $rows
     */
    public function __construct(
        public string $siteUrl,
        public SearchConsoleQuery $query,
        public array $rows,
    ) {
    }
}
