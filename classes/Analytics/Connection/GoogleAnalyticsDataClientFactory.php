<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Goosialize\Google\Core\Auth\CredentialProviderInterface;
use Goosialize\Google\Core\Auth\CredentialReference;
use RuntimeException;

final class GoogleAnalyticsDataClientFactory
{
    public function __construct(
        private CredentialProviderInterface $credentialProvider
    ) {
    }

    public function create(
        CredentialReference $reference
    ): GoogleAnalyticsDataClient {
        if (
            !$this->credentialProvider
                ->supports(
                    $reference->type()
                )
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

        $client = new BetaAnalyticsDataClient([
            'transport' => 'rest',
            'credentials' => $credentials,
        ]);

        return new GoogleAnalyticsDataClient(
            $client
        );
    }
}
