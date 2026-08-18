<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Dashboard;

use Goosialize\Google\SearchConsole\Reporting\SearchConsoleReportingService;

final readonly class SearchConsolePropertyDirectory
{
    public function __construct(
        private SearchConsoleReportingService $reporting
    ) {
    }

    /**
     * @return array{
     *     ok:true,
     *     data:list<array{
     *         site_url:string,
     *         permission_level:?string,
     *         property_type:string
     *     }>
     * }
     */
    public function payload(): array
    {
        $data = [];

        foreach (
            $this->reporting->listProperties()
            as $property
        ) {
            $data[] = [
                'site_url' =>
                    $property->siteUrl,
                'permission_level' =>
                    $property->permissionLevel,
                'property_type' =>
                    $property->isDomainProperty()
                        ? 'domain'
                        : 'url_prefix',
            ];
        }

        usort(
            $data,
            static fn (
                array $left,
                array $right
            ): int => strcmp(
                $left['site_url'],
                $right['site_url']
            )
        );

        return [
            'ok' => true,
            'data' => $data,
        ];
    }
}
