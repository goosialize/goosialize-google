<?php

declare(strict_types=1);

use Google\Auth\FetchAuthTokenInterface;
use Goosialize\Google\SearchConsole\Connection\GoogleSearchConsoleClient;
use Goosialize\Google\SearchConsole\Connection\SearchConsoleApiException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require dirname(__DIR__, 2)
    . '/vendor/autoload.php';

$assertions = 0;

$assert = static function (
    bool $condition,
    string $message
) use (&$assertions): void {
    ++$assertions;

    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$credentials =
    new class implements FetchAuthTokenInterface {
        public function fetchAuthToken(
            ?callable $httpHandler = null
        ): array {
            return [
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ];
        }

        public function getCacheKey(): string
        {
            return 'search-console-test';
        }

        public function getLastReceivedToken():
            ?array
        {
            return null;
        }
    };

$http =
    new class implements ClientInterface {
        /**
         * @var list<array{
         *     method:string,
         *     uri:string,
         *     options:array<string,mixed>
         * }>
         */
        public array $requests = [];

        public function sendRequest(
            RequestInterface $request
        ): ResponseInterface {
            throw new RuntimeException(
                'sendRequest is not used by this adapter test.'
            );
        }

        /**
         * Guzzle-compatible request method used by
         * GoogleSearchConsoleClient.
         *
         * @param array<string,mixed> $options
         */
        public function request(
            string $method,
            string $uri,
            array $options = []
        ): ResponseInterface {
            $this->requests[] = [
                'method' => $method,
                'uri' => $uri,
                'options' => $options,
            ];

            if ($method === 'GET') {
                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'siteEntry' => [
                            [
                                'siteUrl' =>
                                    'sc-domain:example.com',
                                'permissionLevel' =>
                                    'siteOwner',
                            ],
                            [
                                'siteUrl' =>
                                    'https://www.example.com/',
                                'permissionLevel' =>
                                    'siteFullUser',
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR)
                );
            }

            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'rows' => [
                        [
                            'keys' => ['grav cms'],
                            'clicks' => 10,
                            'impressions' => 100,
                            'ctr' => 0.1,
                            'position' => 2.5,
                        ],
                    ],
                ], JSON_THROW_ON_ERROR)
            );
        }
    };

$client = new GoogleSearchConsoleClient(
    $credentials,
    $http
);

$sites = $client->listSites();

$assert(
    count($sites) === 2,
    'Site discovery count failed.'
);

$assert(
    $sites[0]['siteUrl']
        === 'sc-domain:example.com',
    'Domain property siteUrl failed.'
);

$assert(
    $sites[0]['permissionLevel']
        === 'siteOwner',
    'Permission normalization failed.'
);

$result = $client->query(
    'sc-domain:example.com',
    [
        'startDate' => '2026-08-01',
        'endDate' => '2026-08-07',
        'dimensions' => ['query'],
        'rowLimit' => 1000,
        'startRow' => 0,
    ]
);

$assert(
    isset($result['rows'])
    && count($result['rows']) === 1,
    'Search Analytics query response failed.'
);

$assert(
    count($http->requests) === 2,
    'Unexpected request count.'
);

$assert(
    $http->requests[0]['method']
        === 'GET',
    'Sites request must use GET.'
);

$assert(
    $http->requests[0]['uri']
        === 'https://www.googleapis.com/webmasters/v3/sites',
    'Sites endpoint contract failed.'
);

$assert(
    $http->requests[1]['method']
        === 'POST',
    'Search Analytics request must use POST.'
);

$assert(
    $http->requests[1]['uri']
        === 'https://www.googleapis.com/webmasters/v3/sites/'
        . 'sc-domain%3Aexample.com'
        . '/searchAnalytics/query',
    'Search Analytics endpoint encoding failed.'
);

$headers =
    $http->requests[1]['options']['headers']
    ?? [];

$assert(
    ($headers['Authorization'] ?? null)
        === 'Bearer test-token',
    'Bearer authorization header failed.'
);

$body =
    $http->requests[1]['options']['body']
    ?? '';

$payload = json_decode(
    (string) $body,
    true,
    512,
    JSON_THROW_ON_ERROR
);

$assert(
    $payload['dimensions'] === ['query'],
    'Search Analytics JSON payload failed.'
);

$emptyTokenCredentials =
    new class implements FetchAuthTokenInterface {
        public function fetchAuthToken(
            ?callable $httpHandler = null
        ): array {
            return [];
        }

        public function getCacheKey(): string
        {
            return 'empty-token';
        }

        public function getLastReceivedToken():
            ?array
        {
            return null;
        }
    };

$tokenFailure = false;

try {
    (
        new GoogleSearchConsoleClient(
            $emptyTokenCredentials,
            $http
        )
    )->listSites();
} catch (SearchConsoleApiException $e) {
    $tokenFailure = str_contains(
        $e->getMessage(),
        'access token'
    );
}

$assert(
    $tokenFailure,
    'Missing access token must fail closed.'
);

echo sprintf(
    "SEARCH_CONSOLE_API_CLIENT_ASSERTIONS=%d\n",
    $assertions
);

echo "SEARCH_CONSOLE_API_CLIENT=PASS\n";
