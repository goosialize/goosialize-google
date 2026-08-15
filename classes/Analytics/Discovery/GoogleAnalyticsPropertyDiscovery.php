<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Discovery;

use Google\Analytics\Admin\V1beta\Client\AnalyticsAdminServiceClient;
use Google\Analytics\Admin\V1beta\ListAccountSummariesRequest;
use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use RuntimeException;
use Throwable;

final class GoogleAnalyticsPropertyDiscovery
    implements AnalyticsPropertyDiscoveryInterface
{
    public function __construct(
        private AnalyticsAdminServiceClient $client
    ) {
    }

    public function discover(): array
    {
        try {
            $response = $this->client
                ->listAccountSummaries(
                    new ListAccountSummariesRequest()
                );

            $properties = [];

            foreach (
                $response->iterateAllElements()
                as $account
            ) {
                $accountResource = trim(
                    $account->getAccount()
                );

                $accountName = trim(
                    $account->getDisplayName()
                );

                foreach (
                    $account->getPropertySummaries()
                    as $property
                ) {
                    $resource = trim(
                        $property->getProperty()
                    );

                    if (
                        !preg_match(
                            '#^properties/([1-9][0-9]*)$#',
                            $resource,
                            $matches
                        )
                    ) {
                        continue;
                    }

                    $properties[] =
                        new AnalyticsPropertySummary(
                            $accountResource,
                            $accountName,
                            new AnalyticsPropertyId(
                                $matches[1]
                            ),
                            $property->getDisplayName()
                        );
                }
            }

            return $properties;
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Google Analytics property discovery failed.',
                0,
                $e
            );
        }
    }
}
