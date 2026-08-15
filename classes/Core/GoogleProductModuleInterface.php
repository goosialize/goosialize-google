<?php

declare(strict_types=1);

namespace Goosialize\Google\Core;

interface GoogleProductModuleInterface
{
    public function getId(): string;

    public function isEnabled(): bool;
}
