<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Events\PermissionsRegisterEvent;
use Grav\Framework\Acl\PermissionsReader;
use Goosialize\Google\Admin\AnalyticsDashboardController;
use RocketTheme\Toolbox\Event\Event;
use Throwable;

$composerAutoload =
    __DIR__ . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

unset($composerAutoload);

final class GoosializeGooglePlugin extends Plugin
{
    private const PERMISSION =
        'api.goosialize_google.analytics.read';

    public static function getSubscribedEvents(): array
    {
        return [
            PermissionsRegisterEvent::class => [
                'onRegisterPermissions',
                1000,
            ],
            'onApiRegisterRoutes' => [
                'onApiRegisterRoutes',
                0,
            ],
            'onApiSidebarItems' => [
                'onApiSidebarItems',
                0,
            ],
            'onApiPluginPageInfo' => [
                'onApiPluginPageInfo',
                0,
            ],
            'onPluginsInitialized' => [
                'onPluginsInitialized',
                0,
            ],
        ];
    }

    public function onPluginsInitialized(): void
    {
    }

    public function onRegisterPermissions(
        PermissionsRegisterEvent $event
    ): void {
        $event->permissions->addActions(
            PermissionsReader::fromYaml(
                "plugin://{$this->name}/permissions.yaml"
            )
        );
    }

    public function onApiRegisterRoutes(
        Event $event
    ): void {
        $routes =
            $event['routes']
            ?? null;

        if (
            !is_object($routes)
            || !method_exists(
                $routes,
                'get'
            )
        ) {
            return;
        }

        $routes->get(
            '/goosialize-google/properties',
            [
                AnalyticsDashboardController::class,
                'properties',
            ]
        );

        $routes->get(
            '/goosialize-google/analytics',
            [
                AnalyticsDashboardController::class,
                'analytics',
            ]
        );
    }

    public function onApiSidebarItems(
        Event $event
    ): void {
        if (!$this->dashboardEnabled()) {
            return;
        }

        $user =
            $event['user']
            ?? null;

        if (!$this->dashboardAllowed($user)) {
            return;
        }

        $items =
            $event['items']
            ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $items[] = [
            'id' =>
                'goosialize-google',
            'plugin' =>
                'goosialize-google',
            'label' =>
                'Google Analytics',
            'icon' =>
                'fa-chart-line',
            'route' =>
                '/plugin/goosialize-google',
            'priority' =>
                18,
            'badge' =>
                null,
            'authorize' =>
                self::PERMISSION,
        ];

        $event['items'] =
            $items;
    }

    public function onApiPluginPageInfo(
        Event $event
    ): void {
        if (
            ($event['plugin'] ?? null)
                !== 'goosialize-google'
            || !$this->dashboardEnabled()
            || !$this->dashboardAllowed(
                $event['user'] ?? null
            )
        ) {
            return;
        }

        $event['definition'] = [
            'id' =>
                'goosialize-google',
            'plugin' =>
                'goosialize-google',
            'title' =>
                'Google Analytics',
            'icon' =>
                'fa-chart-line',
            'page_type' =>
                'component',
            'actions' => [
                [
                    'id' =>
                        'refresh',
                    'label' =>
                        'Refresh',
                    'icon' =>
                        'fa-refresh',
                ],
            ],
        ];
    }

    private function dashboardEnabled(): bool
    {
        return (bool) $this->grav[
            'config'
        ]->get(
            'plugins.goosialize-google.enabled',
            true
        )
            && (bool) $this->grav[
                'config'
            ]->get(
                'plugins.goosialize-google.analytics.enabled',
                true
            );
    }

    private function dashboardAllowed(
        mixed $user
    ): bool {
        if (!is_object($user)) {
            return false;
        }

        try {
            if (
                method_exists(
                    $user,
                    'get'
                )
            ) {
                if (
                    (bool) $user->get(
                        'access.admin.super'
                    )
                    || (bool) $user->get(
                        'access.api.super'
                    )
                ) {
                    return true;
                }

                if (
                    !(bool) $user->get(
                        'access.api.access'
                    )
                ) {
                    return false;
                }

                if (
                    (bool) $user->get(
                        'access.'
                        . self::PERMISSION
                    )
                ) {
                    return true;
                }
            }

            if (
                method_exists(
                    $user,
                    'authorize'
                )
            ) {
                if (
                    (bool) $user->authorize(
                        'api.super'
                    )
                ) {
                    return true;
                }

                if (
                    !(bool) $user->authorize(
                        'api.access'
                    )
                ) {
                    return false;
                }

                return (bool) $user->authorize(
                    self::PERMISSION
                );
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }
}
