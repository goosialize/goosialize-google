<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Testing;

use Goosialize\Google\SearchConsole\Connection\SearchConsoleClientInterface;

final class FakeSearchConsoleClient implements SearchConsoleClientInterface
{
    /**
     * @param list<array{
     *     siteUrl:string,
     *     permissionLevel:?string
     * }> $sites
     * @param array<string, mixed> $response
     */
    public function __construct(
        private array $sites = [],
        private array $response = [],
        private array $sitemaps = [],
    ) {
    }

    /**
     * @return list<array{
     *     siteUrl:string,
     *     permissionLevel:?string
     * }>
     */
    public function listSites(): array
    {
        return $this->sites;
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function listSitemaps(
        string $siteUrl
    ): array {
        return $this->sitemaps;
    }

    public function query(
        string $siteUrl,
        array $request
    ): array {
        return $this->response;
    }
}
