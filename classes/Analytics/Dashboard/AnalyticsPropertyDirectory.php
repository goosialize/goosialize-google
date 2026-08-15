<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Dashboard;

use Goosialize\Google\Analytics\Discovery\AnalyticsPropertyDiscoveryInterface;

final class AnalyticsPropertyDirectory
{
    public function __construct(
        private AnalyticsPropertyDiscoveryInterface $discovery
    ) {
    }

    /**
     * @return array{
     *   ok:true,
     *   data:list<array{
     *     account:string,
     *     account_name:string,
     *     property_id:string,
     *     property_name:string
     *   }>
     * }
     */
    public function payload(): array
    {
        $data = [];

        foreach (
            $this->discovery->discover()
            as $property
        ) {
            $data[] = [
                'account' =>
                    $property->accountResource(),
                'account_name' =>
                    $property->accountName(),
                'property_id' =>
                    $property->propertyId()->value(),
                'property_name' =>
                    $property->propertyName(),
            ];
        }

        usort(
            $data,
            static function (
                array $left,
                array $right
            ): int {
                $account = strcmp(
                    $left['account_name'],
                    $right['account_name']
                );

                if ($account !== 0) {
                    return $account;
                }

                return strcmp(
                    $left['property_name'],
                    $right['property_name']
                );
            }
        );

        return [
            'ok' => true,
            'data' => $data,
        ];
    }
}
