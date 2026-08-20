<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use InvalidArgumentException;

final class ReportCatalog
{
    /**
     * @return list<ReportDefinition>
     */
    public function all(): array
    {
        return [
            $this->overview(),
            $this->trend(),
            $this->topPages(),
            $this->trafficChannels(),
            $this->devices(),
            $this->operatingSystems(),
            $this->countries(),
            $this->cities(),
            $this->busyDays(),
            $this->busyHours(),
            $this->gender(),
            $this->ages(),
            $this->events(),
        ];
    }

    public function get(string $id): ReportDefinition
    {
        foreach ($this->all() as $definition) {
            if ($definition->id() === $id) {
                return $definition;
            }
        }

        throw new InvalidArgumentException(
            'Unknown report definition: '
            . $id
        );
    }

    public function overview(): ReportDefinition
    {
        return new ReportDefinition(
            'overview',
            [],
            [
                'activeUsers',
                'newUsers',
                'sessions',
                'screenPageViews',
                'engagementRate',
                'averageSessionDuration',
                'eventCount',
                'keyEvents',
            ],
            1
        );
    }

    public function trend(): ReportDefinition
    {
        return new ReportDefinition(
            'trend',
            [
                'date',
            ],
            [
                'screenPageViews',
                'sessions',
                'eventCount',
                'engagementRate',
                'averageSessionDuration',
            ],
            100
        );
    }

    public function topPages(): ReportDefinition
    {
        return new ReportDefinition(
            'top_pages',
            [
                'pagePath',
                'pageTitle',
            ],
            [
                'screenPageViews',
                'activeUsers',
                'averageSessionDuration',
            ],
            50
        );
    }

    public function trafficChannels(): ReportDefinition
    {
        return new ReportDefinition(
            'traffic_channels',
            [
                'sessionDefaultChannelGroup',
            ],
            [
                'sessions',
                'activeUsers',
                'engagementRate',
                'keyEvents',
            ],
            50
        );
    }

    public function devices(): ReportDefinition
    {
        return new ReportDefinition(
            'devices',
            [
                'deviceCategory',
            ],
            [
                'sessions',
                'activeUsers',
            ],
            20
        );
    }

    public function operatingSystems(): ReportDefinition
    {
        return new ReportDefinition(
            'operating_systems',
            [
                'operatingSystem',
            ],
            [
                'sessions',
                'activeUsers',
            ],
            20
        );
    }

    public function cities(): ReportDefinition
    {
        return new ReportDefinition(
            'cities',
            [
                'country',
                'city',
            ],
            [
                'activeUsers',
                'sessions',
            ],
            100
        );
    }

    public function busyDays(): ReportDefinition
    {
        return new ReportDefinition(
            'busy_days',
            [
                'dayOfWeekName',
            ],
            [
                'sessions',
                'activeUsers',
            ],
            7
        );
    }

    public function busyHours(): ReportDefinition
    {
        return new ReportDefinition(
            'busy_hours',
            [
                'hour',
            ],
            [
                'sessions',
                'activeUsers',
            ],
            24
        );
    }

    public function gender(): ReportDefinition
    {
        return new ReportDefinition(
            'gender',
            [
                'userGender',
            ],
            [
                'activeUsers',
            ],
            10
        );
    }

    public function ages(): ReportDefinition
    {
        return new ReportDefinition(
            'ages',
            [
                'userAgeBracket',
            ],
            [
                'activeUsers',
            ],
            10
        );
    }

    public function countries(): ReportDefinition
    {
        return new ReportDefinition(
            'countries',
            [
                'country',
            ],
            [
                'activeUsers',
                'sessions',
            ],
            100
        );
    }

    public function events(): ReportDefinition
    {
        return new ReportDefinition(
            'events',
            [
                'eventName',
            ],
            [
                'eventCount',
                'totalUsers',
            ],
            100
        );
    }
}
