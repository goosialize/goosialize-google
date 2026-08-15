<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Auth;

interface CredentialProviderInterface
{
    public function supports(CredentialType $type): bool;

    /**
     * @return array<string, mixed>
     */
    public function resolve(
        CredentialReference $reference
    ): array;
}
