<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Plugin;

final class GoosializeGooglePlugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }
    }
}
