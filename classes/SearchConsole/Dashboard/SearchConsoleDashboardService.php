<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Dashboard;

use DateTimeImmutable;
use Goosialize\Google\SearchConsole\Connection\SearchConsoleProperty;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleDateRange;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleDimension;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleQuery;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleReportingService;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleRow;
use RuntimeException;

final readonly class SearchConsoleDashboardService
{
    public function __construct(
        private SearchConsoleReportingService $reporting
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function dashboard(
        string $siteUrl,
        SearchConsoleDashboardPeriod $period,
        DateTimeImmutable $today
    ): array {
        $property =
            $this->findProperty(
                $siteUrl
            );

        $range =
            new SearchConsoleDateRange(
                $today->modify(
                    sprintf(
                        '-%d days',
                        $period->value - 1
                    )
                ),
                $today
            );

        $overview =
            $this->reporting->execute(
                $property,
                new SearchConsoleQuery(
                    $range,
                    [],
                    1
                )
            );

        $topQueries =
            $this->reporting->execute(
                $property,
                new SearchConsoleQuery(
                    $range,
                    [
                        SearchConsoleDimension::Query,
                    ],
                    25
                )
            );

        $topPages =
            $this->reporting->execute(
                $property,
                new SearchConsoleQuery(
                    $range,
                    [
                        SearchConsoleDimension::Page,
                    ],
                    25
                )
            );

        $devices =
            $this->reporting->execute(
                $property,
                new SearchConsoleQuery(
                    $range,
                    [
                        SearchConsoleDimension::Device,
                    ],
                    25
                )
            );

        $countries =
            $this->reporting->execute(
                $property,
                new SearchConsoleQuery(
                    $range,
                    [
                        SearchConsoleDimension::Country,
                    ],
                    25
                )
            );

        $metrics = [
            'clicks' => 0.0,
            'impressions' => 0.0,
            'ctr' => 0.0,
            'position' => 0.0,
        ];

        if (isset($overview->rows[0])) {
            $metrics =
                $this->metrics(
                    $overview->rows[0]
                );
        }

        $empty =
            $overview->rows === []
            && $topQueries->rows === []
            && $topPages->rows === [];

        return [
            'ok' => true,

            'property' => [
                'site_url' =>
                    $property->siteUrl,
                'permission_level' =>
                    $property->permissionLevel,
                'type' =>
                    $property->isDomainProperty()
                        ? 'domain'
                        : 'url_prefix',
            ],

            'period' => [
                'days' =>
                    $period->value,
                'start_date' =>
                    $range
                        ->startDate
                        ->format('Y-m-d'),
                'end_date' =>
                    $range
                        ->endDate
                        ->format('Y-m-d'),
            ],

            'empty' =>
                $empty,

            'overview' =>
                $metrics,

            'top_queries' =>
                $this->rows(
                    $topQueries->rows,
                    'query'
                ),

            'top_pages' =>
                $this->rows(
                    $topPages->rows,
                    'page'
                ),

            'devices' =>
                $this->rows(
                    $devices->rows,
                    'device'
                ),

            'countries' =>
                $this->rows(
                    $countries->rows,
                    'country'
                ),
        ];
    }

    private function findProperty(
        string $siteUrl
    ): SearchConsoleProperty {
        $candidate = trim($siteUrl);

        if ($candidate === '') {
            throw new RuntimeException(
                'Search Console property is required.'
            );
        }

        foreach (
            $this->reporting->listProperties()
            as $property
        ) {
            if (
                $property->siteUrl
                === $candidate
            ) {
                return $property;
            }
        }

        throw new RuntimeException(
            'Requested Search Console property is not accessible.'
        );
    }

    /**
     * @return array{
     *     clicks:float,
     *     impressions:float,
     *     ctr:float,
     *     position:float
     * }
     */
    private function metrics(
        SearchConsoleRow $row
    ): array {
        return [
            'clicks' =>
                $row->clicks,
            'impressions' =>
                $row->impressions,
            'ctr' =>
                $row->ctr,
            'position' =>
                $row->position,
        ];
    }

    /**
     * @param list<SearchConsoleRow> $rows
     * @return list<array<string,mixed>>
     */
    private function rows(
        array $rows,
        string $dimension
    ): array {
        return array_map(
            fn (
                SearchConsoleRow $row
            ): array => [
                $dimension =>
                    $row->keys[0]
                    ?? '',
                ...$this->metrics(
                    $row
                ),
            ],
            $rows
        );
    }
}
