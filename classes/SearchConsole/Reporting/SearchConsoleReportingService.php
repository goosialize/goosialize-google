<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

use Goosialize\Google\SearchConsole\Connection\SearchConsoleClientInterface;
use Goosialize\Google\SearchConsole\Connection\SearchConsoleProperty;

final readonly class SearchConsoleReportingService
{
    public function __construct(
        private SearchConsoleClientInterface $client,
        private SearchConsoleResponseNormalizer $normalizer,
    ) {
    }

    /**
     * @return list<SearchConsoleProperty>
     */
    public function listProperties(): array
    {
        return array_map(
            static fn (array $site): SearchConsoleProperty =>
                new SearchConsoleProperty(
                    (string) ($site['siteUrl'] ?? ''),
                    isset($site['permissionLevel'])
                        ? (string) $site['permissionLevel']
                        : null
                ),
            $this->client->listSites()
        );
    }

    public function execute(
        SearchConsoleProperty $property,
        SearchConsoleQuery $query
    ): SearchConsoleResult {
        $response = $this->client->query(
            $property->siteUrl,
            $query->toApiPayload()
        );

        return new SearchConsoleResult(
            $property->siteUrl,
            $query,
            $this->normalizer->normalize($response)
        );
    }
}
