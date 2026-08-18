<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

interface SearchConsoleClientInterface
{
    /**
     * @return list<array{
     *     siteUrl:string,
     *     permissionLevel:?string
     * }>
     */
    public function listSites(): array;

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function query(
        string $siteUrl,
        array $request
    ): array;
}
