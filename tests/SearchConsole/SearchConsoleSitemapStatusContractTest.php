<?php

declare(strict_types=1);

use Goosialize\Google\SearchConsole\Sitemap\CanonicalSitemapStatusService;
use Goosialize\Google\SearchConsole\Testing\FakeSearchConsoleClient;
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
        throw new RuntimeException(
            $message
        );
    }
};

$client =
    new FakeSearchConsoleClient(
        sites: [],
        response: [],
        sitemaps: [
            'sitemap' => [
                [
                    'path' =>
                        'https://example.test/sitemap.xml',
                    'lastSubmitted' =>
                        '2026-09-10T10:00:00.000Z',
                    'lastDownloaded' =>
                        '2026-09-11T11:00:00.000Z',
                    'isPending' =>
                        false,
                    'isSitemapsIndex' =>
                        false,
                    'type' =>
                        'SITEMAP',
                    'warnings' =>
                        '0',
                    'errors' =>
                        '0',
                ],
            ],
        ],
    );

$http =
    new class implements ClientInterface {
        public function sendRequest(
            RequestInterface $request
        ): ResponseInterface {
            return $this->response();
        }

        public function request(
            string $method,
            string $uri,
            array $options = []
        ): ResponseInterface {
            return $this->response();
        }

        private function response():
            ResponseInterface
        {
            return new Response(
                200,
                [
                    'Content-Type' =>
                        'application/xml; charset=UTF-8',
                    'X-Goosialize-SEO-Sitemap-Consistent' =>
                        'PASS',
                    'X-Goosialize-SEO-Sitemap-URLs' =>
                        '46',
                    'X-Goosialize-SEO-Hash-Sections' =>
                        '0',
                ],
                '<?xml version="1.0"?><urlset/>'
            );
        }
    };

$status =
    (
        new CanonicalSitemapStatusService(
            $client,
            $http
        )
    )->status(
        'sc-domain:example.test',
        'https://example.test/sitemap.xml'
    );

$assert(
    $status['sitemap']['reachable'] === true,
    'Canonical sitemap must be reachable.'
);

$assert(
    $status['sitemap']['consistent'] === true,
    'Canonical sitemap consistency missing.'
);

$assert(
    $status['sitemap']['url_count'] === 46,
    'Canonical sitemap URL count failed.'
);

$assert(
    $status['sitemap']['hash_sections'] === 0,
    'Hash section count failed.'
);

$assert(
    $status['search_console']['submitted'] === true,
    'Search Console submission must be detected.'
);

$assert(
    $status['search_console']['submission']['errors'] === 0,
    'Search Console error normalization failed.'
);

$assert(
    $status['search_console']['submission']['warnings'] === 0,
    'Search Console warning normalization failed.'
);

echo sprintf(
    "SEARCH_CONSOLE_SITEMAP_STATUS_ASSERTIONS=%d\n",
    $assertions
);

echo "SEARCH_CONSOLE_SITEMAP_STATUS=PASS\n";
