<?php

declare(strict_types=1);

namespace Goosialize\Google\Admin;

use DateTimeImmutable;
use Grav\Common\Config\Config;
use Grav\Framework\Psr7\Response;
use Goosialize\Google\Analytics\Connection\GoogleAnalyticsDataClientFactory;
use Goosialize\Google\Analytics\Dashboard\AnalyticsDashboardService;
use Goosialize\Google\Analytics\Dashboard\AnalyticsPropertyDirectory;
use Goosialize\Google\Analytics\Dashboard\DashboardPeriod;
use Goosialize\Google\Analytics\Discovery\GoogleAnalyticsPropertyDiscoveryFactory;
use Goosialize\Google\Analytics\Reporting\AnalyticsReportingService;
use Goosialize\Google\Analytics\Reporting\ComparisonEngine;
use Goosialize\Google\Analytics\Reporting\DateRangeFactory;
use Goosialize\Google\Analytics\Reporting\MetadataNormalizer;
use Goosialize\Google\Analytics\Reporting\ReportCatalog;
use Goosialize\Google\Analytics\Reporting\ReportCompatibilityValidator;
use Goosialize\Google\Analytics\Reporting\ReportRequestBuilder;
use Goosialize\Google\Analytics\Reporting\ReportResponseNormalizer;
use Goosialize\Google\Core\Auth\CredentialReference;
use Goosialize\Google\Core\Auth\CredentialType;
use Goosialize\Google\Core\Auth\ServiceAccountCredentialProvider;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

final class AnalyticsDashboardController
{
    private const PERMISSION =
        'api.goosialize_google.analytics.read';

    public function __construct(
        private readonly Config $config
    ) {
    }

    public function properties(
        ServerRequestInterface $request
    ): ResponseInterface {
        $user =
            $request->getAttribute(
                'api_user'
            );

        if (!is_object($user)) {
            return $this->failure(
                401,
                'authentication_required',
                'Authentication required.'
            );
        }

        if (!$this->authorized($request, $user)) {
            return $this->failure(
                403,
                'forbidden',
                'Google Analytics access is forbidden.'
            );
        }

        try {
            $discovery =
                $this->discovery();

            return $this->response(
                200,
                (
                    new AnalyticsPropertyDirectory(
                        $discovery
                    )
                )->payload()
            );
        } catch (Throwable $e) {
            return $this->mappedFailure(
                $e,
                'properties_unavailable'
            );
        }
    }

    public function analytics(
        ServerRequestInterface $request
    ): ResponseInterface {
        $user =
            $request->getAttribute(
                'api_user'
            );

        if (!is_object($user)) {
            return $this->failure(
                401,
                'authentication_required',
                'Authentication required.'
            );
        }

        if (!$this->authorized($request, $user)) {
            return $this->failure(
                403,
                'forbidden',
                'Google Analytics access is forbidden.'
            );
        }

        $query =
            $request->getQueryParams();

        $propertyId =
            $query['property_id']
            ?? $this->config->get(
                'plugins.goosialize-google.analytics.default_property'
            );

        if (
            !is_string($propertyId)
            || trim($propertyId) === ''
        ) {
            return $this->failure(
                400,
                'property_required',
                'A GA4 property is required.'
            );
        }

        $daysRaw =
            $query['days']
            ?? 30;

        if (
            is_string($daysRaw)
            && ctype_digit($daysRaw)
        ) {
            $daysRaw =
                (int) $daysRaw;
        }

        if (!is_int($daysRaw)) {
            return $this->failure(
                400,
                'period_invalid',
                'Analytics period is invalid.'
            );
        }

        try {
            $period =
                DashboardPeriod::fromDays(
                    $daysRaw
                );

            $discovery =
                $this->discovery();

            $credential =
                $this->credentialReference();

            $provider =
                new ServiceAccountCredentialProvider();

            $dataClient =
                (
                    new GoogleAnalyticsDataClientFactory(
                        $provider
                    )
                )->create(
                    $credential
                );

            $dateRanges =
                new DateRangeFactory();

            $reporting =
                new AnalyticsReportingService(
                    $dataClient,
                    new ReportCatalog(),
                    new ReportRequestBuilder(),
                    new ReportResponseNormalizer(),
                    new MetadataNormalizer(),
                    new ReportCompatibilityValidator(),
                    $dateRanges,
                    new ComparisonEngine()
                );

            $dashboard =
                new AnalyticsDashboardService(
                    $discovery,
                    $reporting,
                    $dateRanges
                );

            $payload =
                $dashboard->dashboard(
                    trim($propertyId),
                    $period,
                    new DateTimeImmutable('today')
                );

            return $this->response(
                200,
                $payload
            );
        } catch (InvalidArgumentException $e) {
            return $this->failure(
                400,
                'request_invalid',
                'Analytics request is invalid.'
            );
        } catch (Throwable $e) {
            return $this->mappedFailure(
                $e,
                'analytics_unavailable'
            );
        }
    }

    private function discovery(): \Goosialize\Google\Analytics\Discovery\AnalyticsPropertyDiscoveryInterface
    {
        $provider =
            new ServiceAccountCredentialProvider();

        return (
            new GoogleAnalyticsPropertyDiscoveryFactory(
                $provider
            )
        )->create(
            $this->credentialReference()
        );
    }

    private function credentialReference(): CredentialReference
    {
        $path =
            $this->config->get(
                'plugins.goosialize-google.authentication.service_account_file'
            );

        if (
            !is_string($path)
            || trim($path) === ''
        ) {
            throw new RuntimeException(
                'Google credential path is not configured.'
            );
        }

        return new CredentialReference(
            CredentialType::SERVICE_ACCOUNT_FILE,
            trim($path)
        );
    }

    private function authorized(
        ServerRequestInterface $request,
        object $user
    ): bool {
        try {
            $scopes =
                $request->getAttribute(
                    'api_key_scopes'
                );

            if (
                is_array($scopes)
                && $scopes !== []
                && !$this->scopesPermit(
                    $scopes,
                    self::PERMISSION
                )
            ) {
                return false;
            }

            if (
                $this->userAuthorized(
                    $user,
                    'api.super'
                )
            ) {
                return true;
            }

            if (
                !$this->userAuthorized(
                    $user,
                    'api.access'
                )
            ) {
                return false;
            }

            return $this->userAuthorized(
                $user,
                self::PERMISSION
            );
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<int,mixed> $scopes
     */
    private function scopesPermit(
        array $scopes,
        string $permission
    ): bool {
        foreach ($scopes as $scope) {
            if (
                !is_string($scope)
                || $scope === ''
            ) {
                continue;
            }

            if (
                $scope === '*'
                || $scope === $permission
                || str_starts_with(
                    $permission,
                    $scope . '.'
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function userAuthorized(
        object $user,
        string $permission
    ): bool {
        if (
            method_exists($user, 'get')
            && $user->get(
                'access.' . $permission
            ) === true
        ) {
            return true;
        }

        return method_exists(
            $user,
            'authorize'
        )
            && $user->authorize(
                $permission
            ) === true;
    }

    private function mappedFailure(
        Throwable $error,
        string $fallbackCode
    ): ResponseInterface {
        $message =
            $error->getMessage();

        if (
            str_contains(
                $message,
                'credential'
            )
        ) {
            return $this->failure(
                503,
                'credentials_unavailable',
                'Google Analytics credentials are unavailable.'
            );
        }

        if (
            str_contains(
                $message,
                'not accessible'
            )
        ) {
            return $this->failure(
                403,
                'property_forbidden',
                'The selected GA4 property is not accessible.'
            );
        }

        return $this->failure(
            503,
            $fallbackCode,
            'Google Analytics data is currently unavailable.'
        );
    }

    private function failure(
        int $status,
        string $code,
        string $message
    ): ResponseInterface {
        return $this->response(
            $status,
            [
                'ok' => false,
                'code' => $code,
                'message' => $message,
            ]
        );
    }

    /**
     * @param array<string,mixed> $body
     */
    private function response(
        int $status,
        array $body
    ): ResponseInterface {
        return new Response(
            $status,
            [
                'Content-Type' =>
                    'application/json; charset=utf-8',
                'Cache-Control' =>
                    'no-store',
                'X-Content-Type-Options' =>
                    'nosniff',
            ],
            json_encode(
                $body,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            )
        );
    }
}
