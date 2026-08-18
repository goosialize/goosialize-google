<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole;

use Goosialize\Google\Core\GoogleProductModuleInterface;

final class SearchConsoleModule
    implements GoogleProductModuleInterface
{
    public function getId(): string
    {
        return 'search_console';
    }

    public function isEnabled(): bool
    {
        return true;
    }
}
