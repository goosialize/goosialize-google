<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Discovery;

use Google\Analytics\Admin\V1beta\Client\AnalyticsAdminServiceClient;
use Goosialize\Google\Core\Auth\CredentialProviderInterface;
use Goosialize\Google\Core\Auth\CredentialReference;
use RuntimeException;

final class GoogleAnalyticsPropertyDiscoveryFactory
{
    public function __construct(
        private CredentialProviderInterface $credentialProvider
    ) {
    }

    public function create(
        CredentialReference $reference
    ): GoogleAnalyticsPropertyDiscovery {
        if (
            !$this->credentialProvider
                ->supports($reference->type())
        ) {
            throw new RuntimeException(
                'Credential provider does not support this credential type.'
            );
        }

        $resolved =
            $this->credentialProvider
                ->resolve($reference);

        $credentials =
            $resolved['credentials']
            ?? null;

        if ($credentials === null) {
            throw new RuntimeException(
                'Credential provider did not return credentials.'
            );
        }

        $client =
            new AnalyticsAdminServiceClient([
                'transport' => 'rest',
                'credentials' => $credentials,
            ]);

        return new GoogleAnalyticsPropertyDiscovery(
            $client
        );
    }
}
