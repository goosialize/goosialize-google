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
            || stripos(
                $html,
                'googletagmanager.com/gtag/js?id='
                . $measurementId
            ) !== false
            || stripos(
                $html,
                $measurementId
            ) !== false
            && stripos(
                $html,
                "gtag('config'"
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
<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s" data-goosialize-google-tracking="1"></script>
<script data-goosialize-google-tracking="1">
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '%1$s');
</script>
HTML,
            $measurementId
        );
    }
}
