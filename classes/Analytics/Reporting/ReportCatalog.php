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
            $this->countries(),
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
                'activeUsers',
                'sessions',
                'screenPageViews',
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
