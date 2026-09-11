<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Tracking;

final class AnalyticsTrackingInjector
{
    public const MARKER =
        'data-goosialize-google-tracking="1"';

    public function normalizeMeasurementId(
        string $measurementId
    ): string {
        return strtoupper(
            trim(
                $measurementId
            )
        );
    }

    public function validMeasurementId(
        string $measurementId
    ): bool {
        $measurementId =
            $this->normalizeMeasurementId(
                $measurementId
            );

        return preg_match(
            '/^G-[A-Z0-9]+$/',
            $measurementId
        ) === 1;
    }

    public function inject(
        string $html,
        bool $enabled,
        string $measurementId,
        array $options = []
    ): string {
        if (!$enabled) {
            return $html;
        }

        $measurementId =
            $this->normalizeMeasurementId(
                $measurementId
            );

        if (
            !$this->validMeasurementId(
                $measurementId
            )
        ) {
            return $html;
        }

        if (
            stripos(
                $html,
                self::MARKER
            ) !== false
        ) {
            return $html;
        }

        $headClose =
            stripos(
                $html,
                '</head>'
            );

        if ($headClose === false) {
            return $html;
        }

        $snippet =
            $this->snippet(
                $measurementId,
                $options
            );

        return substr(
            $html,
            0,
            $headClose
        )
            . $snippet
            . "\n"
            . substr(
                $html,
                $headClose
            );
    }

    private function snippet(
        string $measurementId,
        array $options
    ): string {
        $config =
            $this->normalizeOptions(
                $options
            );

        $configJson =
            json_encode(
                $config,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_THROW_ON_ERROR
            );

        $measurementJson =
            json_encode(
                $measurementId,
                JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_THROW_ON_ERROR
            );

        return sprintf(
            <<<'HTML'
<script data-goosialize-google-tracking="1">
(() => {
  'use strict';

  const measurementId = %1$s;
  const baseConfig = %2$s;

  let started = false;
  let analyticsGranted = false;
  let marketingGranted = false;

  window.dataLayer =
    window.dataLayer || [];

  window.gtag =
    window.gtag || function () {
      window.dataLayer.push(
        arguments
      );
    };

  window.gtag(
    'consent',
    'default',
    {
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    }
  );

  function consentUpdate() {
    window.gtag(
      'consent',
      'update',
      {
        analytics_storage:
          analyticsGranted
            ? 'granted'
            : 'denied',

        ad_storage:
          marketingGranted
            ? 'granted'
            : 'denied',

        ad_user_data:
          marketingGranted
            ? 'granted'
            : 'denied',

        ad_personalization:
          marketingGranted
            ? 'granted'
            : 'denied'
      }
    );
  }

  function start() {
    if (
      started
      || analyticsGranted !== true
    ) {
      return;
    }

    started = true;

    const script =
      document.createElement(
        'script'
      );

    script.async = true;

    script.src =
      'https://www.googletagmanager.com/gtag/js?id='
      + encodeURIComponent(
          measurementId
        );

    script.setAttribute(
      'data-goosialize-google-tracking-loader',
      '1'
    );

    document.head.appendChild(
      script
    );

    window.gtag(
      'js',
      new Date()
    );

    consentUpdate();

    if (
      baseConfig.event_parameters
      && Object.keys(
        baseConfig.event_parameters
      ).length > 0
    ) {
      window.gtag(
        'set',
        baseConfig.event_parameters
      );
    }

    if (
      baseConfig.user_properties
      && Object.keys(
        baseConfig.user_properties
      ).length > 0
    ) {
      window.gtag(
        'set',
        'user_properties',
        baseConfig.user_properties
      );
    }

    const config = {
      ...baseConfig
    };

    delete config.event_parameters;
    delete config.user_properties;

    window.gtag(
      'config',
      measurementId,
      config
    );
  }

  function apply(state) {
    analyticsGranted =
      state?.analytics === true;

    marketingGranted =
      state?.marketing === true;

    consentUpdate();

    if (analyticsGranted) {
      start();
    }
  }

  function currentConsent() {
    const adapter =
      window.GoosializeGoogleConsent;

    if (
      !adapter
      || typeof
        adapter.getState
        !== 'function'
    ) {
      return {
        analytics: false,
        marketing: false
      };
    }

    return adapter.getState();
  }

  window.addEventListener(
    'goosialize-google:consent-ready',
    event => {
      apply(
        event?.detail?.current
      );
    }
  );

  window.addEventListener(
    'goosialize-google:consent-changed',
    event => {
      apply(
        event?.detail?.current
      );
    }
  );

  apply(
    currentConsent()
  );
})();
</script>
HTML,
            $measurementJson,
            $configJson
        );
    }

    private function normalizeOptions(
        array $options
    ): array {
        $config = [
            'send_page_view' =>
                (bool) (
                    $options[
                        'send_page_view'
                    ]
                    ?? true
                ),

            'allow_google_signals' =>
                (bool) (
                    $options[
                        'allow_google_signals'
                    ]
                    ?? false
                ),

            'allow_ad_personalization_signals' =>
                (bool) (
                    $options[
                        'allow_ad_personalization_signals'
                    ]
                    ?? false
                ),

            'debug_mode' =>
                (bool) (
                    $options[
                        'debug_mode'
                    ]
                    ?? false
                ),
        ];

        $cookieDomain =
            trim(
                (string) (
                    $options[
                        'cookie_domain'
                    ]
                    ?? ''
                )
            );

        if (
            $cookieDomain !== ''
            && preg_match(
                '/^(auto|[A-Za-z0-9.-]+)$/',
                $cookieDomain
            ) === 1
        ) {
            $config['cookie_domain'] =
                $cookieDomain;
        }

        $cookiePrefix =
            trim(
                (string) (
                    $options[
                        'cookie_prefix'
                    ]
                    ?? ''
                )
            );

        if (
            $cookiePrefix !== ''
            && preg_match(
                '/^[A-Za-z0-9_-]+$/',
                $cookiePrefix
            ) === 1
        ) {
            $config['cookie_prefix'] =
                $cookiePrefix;
        }

        $cookieExpires =
            (int) (
                $options[
                    'cookie_expires'
                ]
                ?? 0
            );

        if ($cookieExpires > 0) {
            $config['cookie_expires'] =
                $cookieExpires;
        }

        $domains =
            $this->normalizeDomains(
                $options[
                    'linker_domains'
                ]
                ?? []
            );

        if ($domains !== []) {
            $config['linker'] = [
                'domains' =>
                    $domains,
            ];
        }

        $config['user_properties'] =
            $this->normalizeMap(
                $options[
                    'user_properties'
                ]
                ?? []
            );

        $config['event_parameters'] =
            $this->normalizeMap(
                $options[
                    'event_parameters'
                ]
                ?? [],
                true
            );

        return $config;
    }

    private function normalizeDomains(
        mixed $domains
    ): array {
        if (!is_array($domains)) {
            return [];
        }

        $normalized = [];

        foreach ($domains as $domain) {
            $domain =
                strtolower(
                    trim(
                        (string) $domain
                    )
                );

            if (
                $domain === ''
                || preg_match(
                    '/^[a-z0-9.-]+$/',
                    $domain
                ) !== 1
                || str_starts_with(
                    $domain,
                    '.'
                )
                || str_ends_with(
                    $domain,
                    '.'
                )
            ) {
                continue;
            }

            $normalized[$domain] =
                $domain;
        }

        return array_values(
            $normalized
        );
    }

    private function normalizeMap(
        mixed $values,
        bool $rejectReserved = false
    ): array {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];

        foreach ($values as $key => $value) {
            $key =
                trim(
                    (string) $key
                );

            if (
                preg_match(
                    '/^[A-Za-z][A-Za-z0-9_]{0,39}$/',
                    $key
                ) !== 1
            ) {
                continue;
            }

            $lower =
                strtolower(
                    $key
                );

            if (
                $rejectReserved
                && (
                    str_starts_with(
                        $lower,
                        'google_'
                    )
                    || str_starts_with(
                        $lower,
                        'gtag_'
                    )
                    || str_starts_with(
                        $lower,
                        'gtm_'
                    )
                    || in_array(
                        $lower,
                        [
                            'send_to',
                            'event_callback',
                            'event_timeout',
                        ],
                        true
                    )
                )
            ) {
                continue;
            }

            if (
                is_scalar($value)
                || $value === null
            ) {
                $normalized[$key] =
                    $value;
            }
        }

        return $normalized;
    }
}
