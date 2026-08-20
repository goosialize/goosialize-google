<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Dashboard;

use DateTimeImmutable;
use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Analytics\Discovery\AnalyticsPropertyDiscoveryInterface;
use Goosialize\Google\Analytics\Reporting\AnalyticsReportingService;
use Goosialize\Google\Analytics\Reporting\DateRange;
use Goosialize\Google\Analytics\Reporting\DateRangeFactory;
use RuntimeException;
use Throwable;

final class AnalyticsDashboardService
{
    public function __construct(
        private AnalyticsPropertyDiscoveryInterface $discovery,
        private AnalyticsReportingService $reporting,
        private DateRangeFactory $dateRanges
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(
        string $propertyId,
        DashboardPeriod $period,
        DateTimeImmutable $today
    ): array {
        $selected =
            $this->findProperty(
                $propertyId
            );

        $range =
            $this->dateRanges
                ->trailingDays(
                    $today,
                    $period->value
                );

        $overview =
            $this->reporting->run(
                $selected->propertyId(),
                'overview',
                $range
            );

        $overviewRows =
            $overview
                ->result()
                ->rows();

        $metrics = [];

        if (isset($overviewRows[0])) {
            $metrics =
                $overviewRows[0]
                    ->metrics();
        }

        $trend =
            $this->reporting->run(
                $selected->propertyId(),
                'trend',
                $range
            );

        $topPages =
            $this->reporting->run(
                $selected->propertyId(),
                'top_pages',
                $range
            );

        $channels =
            $this->reporting->run(
                $selected->propertyId(),
                'traffic_channels',
                $range
            );

        $devices =
            $this->reporting->run(
                $selected->propertyId(),
                'devices',
                $range
            );

        $countries =
            $this->reporting->run(
                $selected->propertyId(),
                'countries',
                $range
            );

        $events =
            $this->reporting->run(
                $selected->propertyId(),
                'events',
                $range
            );

        $operatingSystems =
            $this->optionalRows(
                $selected->propertyId(),
                'operating_systems',
                $range
            );

        $cities =
            $this->optionalRows(
                $selected->propertyId(),
                'cities',
                $range
            );

        $busyDays =
            $this->optionalRows(
                $selected->propertyId(),
                'busy_days',
                $range
            );

        $busyHours =
            $this->optionalRows(
                $selected->propertyId(),
                'busy_hours',
                $range
            );

        $gender =
            $this->optionalRows(
                $selected->propertyId(),
                'gender',
                $range
            );

        $ages =
            $this->optionalRows(
                $selected->propertyId(),
                'ages',
                $range
            );

        $empty =
            $overview
                ->result()
                ->rowCount() === 0
            && $topPages
                ->result()
                ->rowCount() === 0
            && $channels
                ->result()
                ->rowCount() === 0;

        return [
            'ok' => true,

            'property' => [
                'id' =>
                    $selected
                        ->propertyId()
                        ->value(),
                'name' =>
                    $selected
                        ->propertyName(),
                'account' =>
                    $selected
                        ->accountName(),
            ],

            'period' => [
                'days' => $period->value,
                'start_date' =>
                    $range
                        ->start()
                        ->format('Y-m-d'),
                'end_date' =>
                    $range
                        ->end()
                        ->format('Y-m-d'),
            ],

            'empty' => $empty,

            'overview' => [
                'activeUsers' =>
                    $metrics['activeUsers']
                    ?? 0,
                'newUsers' =>
                    $metrics['newUsers']
                    ?? 0,
                'sessions' =>
                    $metrics['sessions']
                    ?? 0,
                'screenPageViews' =>
                    $metrics['screenPageViews']
                    ?? 0,
                'engagementRate' =>
                    $metrics['engagementRate']
                    ?? 0,
                'averageSessionDuration' =>
                    $metrics[
                        'averageSessionDuration'
                    ] ?? 0,
                'eventCount' =>
                    $metrics['eventCount']
                    ?? 0,
                'keyEvents' =>
                    $metrics['keyEvents']
                    ?? 0,
            ],

            'trend' =>
                $this->rows(
                    $trend
                        ->result()
                        ->rows()
                ),

            'top_pages' =>
                $this->rows(
                    $topPages
                        ->result()
                        ->rows()
                ),

            'traffic_channels' =>
                $this->rows(
                    $channels
                        ->result()
                        ->rows()
                ),

            'devices' =>
                $this->rows(
                    $devices
                        ->result()
                        ->rows()
                ),

            'countries' =>
                $this->rows(
                    $countries
                        ->result()
                        ->rows()
                ),

            'operating_systems' =>
                $operatingSystems,

            'cities' =>
                $cities,

            'busy_days' =>
                $busyDays,

            'busy_hours' =>
                $busyHours,

            'gender' =>
                $gender,

            'ages' =>
                $ages,

            'events' =>
                $this->rows(
                    $events
                        ->result()
                        ->rows()
                ),
        ];
    }

    private function optionalRows(
        AnalyticsPropertyId $propertyId,
        string $reportId,
        DateRange $range
    ): array {
        try {
            return $this->rows(
                $this->reporting
                    ->run(
                        $propertyId,
                        $reportId,
                        $range
                    )
                    ->result()
                    ->rows()
            );
        } catch (Throwable) {
            return [];
        }
    }

    private function findProperty(
        string $propertyId
    ): \Goosialize\Google\Analytics\Discovery\AnalyticsPropertySummary {
        $candidate =
            new AnalyticsPropertyId(
                $propertyId
            );

        foreach (
            $this->discovery
                ->discover()
            as $property
        ) {
            if (
                $property
                    ->propertyId()
                    ->value()
                === $candidate->value()
            ) {
                return $property;
            }
        }

        throw new RuntimeException(
            'Requested GA4 property is not accessible.'
        );
    }

    /**
     * @param list<\Goosialize\Google\Analytics\Reporting\ReportRow> $rows
     * @return list<array{
     *   dimensions:array<string,string>,
     *   metrics:array<string,float|int|string>
     * }>
     */
    private function rows(
        array $rows
    ): array {
        return array_map(
            static fn ($row): array => [
                'dimensions' =>
                    $row->dimensions(),
                'metrics' =>
                    $row->metrics(),
            ],
            $rows
        );
    }
}
