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
        string $measurementId
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
                $measurementId
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
        string $measurementId
    ): string {
        return sprintf(
            <<<'HTML'
<script data-goosialize-google-tracking="1">
(() => {
  'use strict';

  const measurementId = '%1$s';

  let started = false;
  let granted = false;

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
      analytics_storage: 'denied'
    }
  );

  function start() {
    if (
      started
      || granted !== true
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

    window.gtag(
      'consent',
      'update',
      {
        analytics_storage: 'granted'
      }
    );

    window.gtag(
      'config',
      measurementId
    );
  }

  function apply(state) {
    const next =
      state?.analytics === true;

    granted = next;

    window.gtag(
      'consent',
      'update',
      {
        analytics_storage:
          next
            ? 'granted'
            : 'denied'
      }
    );

    if (next) {
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
        analytics: false
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
            $measurementId
        );
    }
}
