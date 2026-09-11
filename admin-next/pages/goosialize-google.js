(() => {
  'use strict';

  const TAG = window.__GRAV_PAGE_TAG;

  if (
    typeof TAG !== 'string' ||
    TAG === '' ||
    customElements.get(TAG)
  ) {
    return;
  }

  function apiUrl(path) {
    const server = String(
      window.__GRAV_API_SERVER_URL || ''
    ).replace(/\/+$/, '');

    const prefix = String(
      window.__GRAV_API_PREFIX || '/api/v1'
    )
      .replace(/^\/?/, '/')
      .replace(/\/+$/, '');

    const suffix =
      String(path || '').startsWith('/')
        ? String(path)
        : `/${String(path || '')}`;

    return `${server}${prefix}${suffix}`;
  }

  function apiHeaders() {
    const headers = {
      Accept: 'application/json',
    };

    if (window.__GRAV_API_TOKEN) {
      headers['X-API-Token'] =
        window.__GRAV_API_TOKEN;
    }

    return headers;
  }

  function element(
    tag,
    text = ''
  ) {
    const node =
      document.createElement(tag);

    if (text !== '') {
      node.textContent =
        String(text);
    }

    return node;
  }

  function formatInteger(value) {
    const number =
      Number(value || 0);

    return Number.isFinite(number)
      ? Math.round(number)
          .toLocaleString()
      : '0';
  }

  function formatPercent(value) {
    const number =
      Number(value || 0);

    if (!Number.isFinite(number)) {
      return '0%';
    }

    return `${(
      number * 100
    ).toFixed(1)}%`;
  }

  function formatDecimal(
    value,
    digits = 1
  ) {
    const number =
      Number(value || 0);

    return Number.isFinite(number)
      ? number.toFixed(digits)
      : '0.0';
  }

  function formatDuration(value) {
    const seconds =
      Number(value || 0);

    if (!Number.isFinite(seconds)) {
      return '0s';
    }

    if (seconds < 60) {
      return `${Math.round(seconds)}s`;
    }

    const minutes =
      Math.floor(seconds / 60);

    const rest =
      Math.round(seconds % 60);

    return `${minutes}m ${rest}s`;
  }

  function formatGaDate(value) {
    const raw =
      String(value || '');

    if (!/^\d{8}$/.test(raw)) {
      return raw;
    }

    const date =
      new Date(
        Number(raw.slice(0, 4)),
        Number(raw.slice(4, 6)) - 1,
        Number(raw.slice(6, 8))
      );

    return date.toLocaleDateString(
      undefined,
      {
        day: '2-digit',
        month: 'short',
      }
    );
  }

  const GOOGLE_METRIC_HELP = {
    'Clicks':
      'Visits from Google Search results to your site.',
    'Impressions':
      'How often your pages appeared in Google Search results.',
    'CTR':
      'Percentage of impressions that resulted in a click.',
    'Average position':
      'Average ranking position of your highest result in Google Search.',
    'Top queries':
      'Search terms that generated impressions or clicks for your site.',
    'Search pages':
      'Pages appearing most often in Google Search results.',
    'Search devices':
      'Devices used by people who saw or clicked your search results.',
    'Search countries':
      'Countries where your Google Search impressions and clicks originated.',

    'Active users':
      'Unique users who actively engaged with the site during the selected period.',
    'New users':
      'Users visiting the site for the first time during the selected period.',
    'Sessions':
      'Visits started on the site. One visitor may create multiple sessions.',
    'Views':
      'Total page and screen views recorded by Google Analytics.',
    'Engagement':
      'Percentage of sessions that were meaningfully engaged.',
    'Avg. session':
      'Average active time visitors spent during a session.',
    'Events':
      'Tracked interactions recorded by Google Analytics.',
    'Key events':
      'Important events marked as key events in Google Analytics.',
    'Top pages':
      'Pages receiving the most views during the selected period.',
    'Traffic channels':
      'How visitors arrived at the site, such as Direct or Organic Search.',
    'Devices':
      'Device categories visitors used to access the site.',
    'Operating systems':
      'Operating systems used by visitors.',
    'Top countries':
      'Countries with the most active visitors.',
    'Busiest days':
      'Days of the week with the highest number of sessions.',
    'Busiest hours':
      'Hours of the day with the highest number of sessions.',
    'Visitor locations':
      'Visitor location information reported by Google Analytics.',
    'Gender':
      'Available demographic gender groups reported by Google Analytics.',
    'Age':
      'Available demographic age groups reported by Google Analytics.'
  };

  function googleMetricHelp(label) {
    return (
      GOOGLE_METRIC_HELP[label]
      || `Information about ${label}.`
    );
  }

  function googleInfoIcon(
    help,
    label = 'Metric information'
  ) {
    const icon =
      element('span', 'i');

    icon.className =
      'goosialize-google-info-icon';

    icon.setAttribute(
      'role',
      'img'
    );

    icon.setAttribute(
      'aria-label',
      label
    );

    icon.title =
      String(help || '');

    return icon;
  }

  function googleMetricIcon(
    iconClass,
    tone,
    fallback
  ) {
    const box =
      element('div');

    box.style.display = 'flex';
    box.style.alignItems = 'center';
    box.style.justifyContent = 'center';
    box.style.width = '3rem';
    box.style.height = '3rem';
    box.style.flexShrink = '0';
    box.style.borderRadius = '0.75rem';

    const tones = {
      blue: [
        'color-mix(in srgb, #3b82f6 10%, transparent)',
        '#3b82f6',
      ],
      violet: [
        'color-mix(in srgb, #8b5cf6 10%, transparent)',
        '#8b5cf6',
      ],
      teal: [
        'color-mix(in srgb, #14b8a6 10%, transparent)',
        '#14b8a6',
      ],
      amber: [
        'color-mix(in srgb, #f59e0b 10%, transparent)',
        '#f59e0b',
      ],
      green: [
        'color-mix(in srgb, #22c55e 10%, transparent)',
        '#22c55e',
      ],
      pink: [
        'color-mix(in srgb, #ec4899 10%, transparent)',
        '#ec4899',
      ],
      orange: [
        'color-mix(in srgb, #f97316 10%, transparent)',
        '#f97316',
      ],
      cyan: [
        'color-mix(in srgb, #06b6d4 10%, transparent)',
        '#06b6d4',
      ],
    };

    const selected =
      tones[tone]
      || tones.violet;

    box.style.background =
      selected[0];

    box.style.color =
      selected[1];

    const icon =
      document.createElement('i');

    icon.className =
      `fa-solid ${iconClass}`;

    icon.setAttribute(
      'aria-hidden',
      'true'
    );

    icon.style.fontSize =
      '1.1rem';

    const fallbackNode =
      element(
        'span',
        fallback
      );

    fallbackNode.style.display =
      'none';

    fallbackNode.style.fontSize =
      '1rem';

    fallbackNode.style.fontWeight =
      '700';

    box.append(
      icon,
      fallbackNode
    );

    requestAnimationFrame(
      () => {
        const style =
          window.getComputedStyle(icon);

        const hasIcon =
          style
          && style.fontFamily
          && style.fontFamily
            .toLowerCase()
            .includes('awesome');

        if (!hasIcon) {
          icon.style.display =
            'none';

          fallbackNode.style.display =
            'inline';
        }
      }
    );

    return box;
  }

  function googleMetricCard(
    label,
    value,
    iconClass,
    tone,
    fallback
  ) {
    const article =
      element('article');

    article.style.display = 'flex';
    article.style.alignItems = 'center';
    article.style.gap = '0.875rem';
    article.style.padding = '0.7rem 0.9rem';
    article.style.border =
      '1px solid var(--border)';
    article.style.background =
      'var(--card)';
    article.style.borderRadius =
      '0.5rem';
    article.style.minWidth = '0';
    article.style.minHeight = '4.4rem';

    const content =
      element('div');

    content.style.minWidth =
      '0';

    const metric =
      element(
        'div',
        value
      );

    metric.style.fontSize =
      '1.75rem';

    metric.style.fontWeight =
      '600';

    metric.style.lineHeight =
      '1.2';

    metric.style.fontVariantNumeric =
      'tabular-nums';

    metric.style.color =
      'var(--foreground)';

    const caption =
      element(
        'div',
        label
      );

    caption.style.marginTop =
      '0.1rem';

    caption.style.fontSize =
      '0.72rem';

    caption.style.color =
      'var(--muted-foreground)';

    content.append(
      metric,
      caption
    );

    article.append(
      googleMetricIcon(
        iconClass,
        tone,
        fallback
      ),
      content
    );

    return article;
  }

  function ensureDashboardStyles() {
    const id =
      'goosialize-google-dashboard-styles';

    if (
      document.getElementById(id)
    ) {
      return;
    }

    const style =
      document.createElement('style');

    style.id = id;

    style.textContent = `
      .goosialize-google-detail-grid {
        display: grid;
        grid-template-columns:
          minmax(0, 1fr);
        gap: 1rem;
      }

      @media (min-width: 1280px) {
        .goosialize-google-detail-grid {
          grid-template-columns:
            repeat(
              2,
              minmax(0, 1fr)
            );
        }
      }

      .goosialize-google-detail-grid
      > .goosialize-google-full-width {
        grid-column: 1 / -1;
      }

      .goosialize-google-loading-panel {
        display: grid;
        gap: 0.55rem;
        padding: 0.8rem 0.9rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--card);
        transition:
          opacity 180ms ease;
      }

      .goosialize-google-loading-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.72rem;
      }

      .goosialize-google-loading-header strong {
        color: var(--primary);
        font-variant-numeric:
          tabular-nums;
      }

      .goosialize-google-loading-track {
        position: relative;
        width: 100%;
        height: 0.35rem;
        overflow: hidden;
        border-radius: 999px;
        background:
          color-mix(
            in srgb,
            var(--muted-foreground)
            13%,
            transparent
          );
      }

      .goosialize-google-loading-value {
        height: 100%;
        min-width: 0;
        border-radius: inherit;
        background: var(--primary);
        box-shadow:
          0 0 0.55rem
          color-mix(
            in srgb,
            var(--primary)
            45%,
            transparent
          );
        transition:
          width 240ms ease;
      }

      .goosialize-google-loading-value.is-indeterminate {
        width: 33%;
        animation:
          goosialize-google-loading-slide
          0.9s ease-in-out infinite;
      }

      .goosialize-google-loading-panel small {
        color: var(--muted-foreground);
        font-size: 0.64rem;
      }

      @keyframes goosialize-google-loading-slide {
        0% {
          transform:
            translateX(-120%);
        }

        100% {
          transform:
            translateX(320%);
        }
      }

      .goosialize-google-timing-grid {
        width: 100%;
      }

      @media (min-width: 900px) {
        .goosialize-google-timing-grid {
          grid-template-columns:
            repeat(
              2,
              minmax(0, 1fr)
            ) !important;
        }
      }

      .goosialize-google-search-highlights {
        display: grid;
        grid-template-columns:
          minmax(0, 1fr);
        gap: 0.75rem;
      }

      @media (min-width: 720px) {
        .goosialize-google-search-highlights {
          grid-template-columns:
            repeat(
              2,
              minmax(0, 1fr)
            );
        }
      }

      @media (min-width: 1280px) {
        .goosialize-google-search-highlights {
          grid-template-columns:
            repeat(
              4,
              minmax(0, 1fr)
            );
        }
      }

      .goosialize-google-search-highlight {
        display: grid;
        min-width: 0;
        gap: 0.3rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--card);
      }

      .goosialize-google-search-highlight small,
      .goosialize-google-search-highlight span {
        color: var(--muted-foreground);
        font-size: 0.64rem;
      }

      .goosialize-google-search-highlight strong {
        overflow: hidden;
        color: var(--foreground);
        font-size: 0.76rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .goosialize-google-info-icon {
        display: inline-flex;
        width: 0.9rem;
        height: 0.9rem;
        align-items: center;
        justify-content: center;
        margin-right: 0.35rem;
        border: 1px solid
          color-mix(
            in srgb,
            var(--muted-foreground)
            55%,
            transparent
          );
        border-radius: 999px;
        color: var(--muted-foreground);
        font-size: 0.58rem;
        font-style: normal;
        font-weight: 700;
        line-height: 1;
        cursor: help;
        vertical-align: middle;
      }

      .goosialize-google-kpi-info {
        position: absolute;
        top: 0.35rem;
        left: 0.4rem;
        z-index: 2;
        margin: 0;
      }

      .goosialize-google-period-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .goosialize-google-period-refresh {
        flex: 0 0 auto;
        width: 2.35rem;
        height: 2.35rem;
        min-height: 2.35rem;
        align-self: center;
      }

      .goosialize-google-refresh-button {
        display: inline-flex;
        width: 2.45rem;
        height: 2.45rem;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--card);
        color: var(--foreground);
        cursor: pointer;
      }

      .goosialize-google-refresh-icon {
        display: inline-block;
        font-size: 1.05rem;
        line-height: 1;
      }

      @keyframes goosialize-google-refresh-rotate {
        to {
          transform: rotate(360deg);
        }
      }

      @keyframes goosialize-google-refresh-pulse {
        0%, 100% {
          opacity: 0.45;
        }

        50% {
          opacity: 1;
          filter:
            drop-shadow(
              0 0 0.42rem
              color-mix(
                in srgb,
                var(--primary)
                70%,
                transparent
              )
            );
        }
      }

      .goosialize-google-refresh-button.is-loading
      .goosialize-google-refresh-icon {
        color: var(--primary);
        animation:
          goosialize-google-refresh-rotate
          0.8s linear infinite,
          goosialize-google-refresh-pulse
          0.9s ease-in-out infinite;
      }

      .goosialize-google-day-bars {
        display: grid;
        grid-template-columns:
          repeat(7, minmax(0, 1fr));
        min-height: 150px;
        gap: 0.45rem;
        align-items: end;
      }

      .goosialize-google-day-column {
        display: grid;
        grid-template-rows:
          auto 105px auto;
        gap: 0.25rem;
        text-align: center;
      }

      .goosialize-google-day-column > div {
        display: flex;
        align-items: end;
        overflow: hidden;
        border-radius: 0.28rem;
        background:
          color-mix(
            in srgb,
            var(--muted-foreground)
            10%,
            transparent
          );
      }

      .goosialize-google-day-column > div span {
        display: block;
        width: 100%;
        border-radius: inherit;
        background:
          linear-gradient(
            to top,
            color-mix(
              in srgb,
              var(--primary)
              92%,
              transparent
            ),
            color-mix(
              in srgb,
              var(--primary)
              48%,
              transparent
            )
          );
      }

      .goosialize-google-day-column strong,
      .goosialize-google-day-column small {
        font-size: 0.65rem;
      }

      .goosialize-google-hour-donut-layout {
        display: grid;
        grid-template-columns:
          130px minmax(0, 1fr);
        gap: 0.85rem;
        align-items: center;
      }

      .goosialize-google-hour-donut {
        width: 125px;
        height: 125px;
      }

      .goosialize-google-hour-legend {
        display: grid;
        gap: 0.3rem;
      }

      .goosialize-google-hour-legend > div {
        display: grid;
        grid-template-columns:
          0.55rem minmax(0,1fr) auto;
        gap: 0.4rem;
        align-items: center;
        font-size: 0.66rem;
      }

      .goosialize-google-hour-legend i {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
      }

      .goosialize-google-geo-card {
        padding: 0.8rem 0.9rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--card);
      }

      .goosialize-google-geo-card h3 {
        margin: 0 0 0.7rem;
        font-size: 0.82rem;
        font-weight: 600;
      }

      .goosialize-google-geo-body {
        display: grid;
        grid-template-columns:
          minmax(0, 2fr)
          minmax(180px, 1fr);
        gap: 1rem;
        align-items: center;
      }

      .goosialize-google-map-stage {
        position: relative;
        min-height: 220px;
      }

      .goosialize-google-map-stage
      .goosialize-google-geo-map {
        width: 100%;
        max-height: 300px;
        object-fit: contain;
        opacity: 0.32;
      }

      .goosialize-google-map-dot {
        position: absolute;
        width: 0.62rem;
        height: 0.62rem;
        transform:
          translate(-50%, -50%);
        border: 2px solid #fff;
        border-radius: 999px;
        background: var(--primary);
        box-shadow:
          0 0 0 0.22rem
          color-mix(
            in srgb,
            var(--primary)
            22%,
            transparent
          );
        cursor: help;
      }

      .goosialize-google-city-list {
        display: grid;
        gap: 0.4rem;
      }

      .goosialize-google-city-list > strong {
        margin-bottom: 0.2rem;
        font-size: 0.72rem;
      }

      .goosialize-google-city-note {
        display: block;
        margin-top: 0.35rem;
        color: var(--muted-foreground);
        font-size: 0.62rem;
        line-height: 1.4;
      }

      .goosialize-google-city-list > div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.66rem;
      }

      @media (max-width: 799px) {
        .goosialize-google-geo-body {
          grid-template-columns:
            minmax(0, 1fr);
        }
      }

      .goosialize-google-kpi-grid {
        display: grid;
        grid-template-columns:
          minmax(0, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
      }

      @media (min-width: 640px) {
        .goosialize-google-kpi-grid {
          grid-template-columns:
            repeat(
              2,
              minmax(0, 1fr)
            );
        }
      }

      @media (min-width: 1280px) {
        .goosialize-google-kpi-grid {
          grid-template-columns:
            repeat(
              8,
              minmax(0, 1fr)
            );
        }
      }

      .goosialize-google-geo-map {
        width: 100%;
        height: auto;
        max-height: 300px;
        display: block;
      }

      .goosialize-google-admin-insight-grid {
        display: grid;
        grid-template-columns:
          minmax(0, 1fr);
        gap: 0.75rem;
      }

      @media (min-width: 900px) {
        .goosialize-google-admin-insight-grid {
          grid-template-columns:
            repeat(
              2,
              minmax(0, 1fr)
            );
        }
      }

      @media (min-width: 1280px) {
        .goosialize-google-admin-insight-grid {
          grid-template-columns:
            repeat(
              4,
              minmax(0, 1fr)
            );
        }
      }

      .goosialize-google-insight-card {
        border: 1px solid var(--border);
        background: var(--card);
        border-radius: 0.5rem;
        padding: 0.8rem 0.9rem;
      }

      .goosialize-google-insight-card h3 {
        margin: 0 0 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--foreground);
      }

      .goosialize-google-insight-list {
        display: grid;
        gap: 0.65rem;
      }

      .goosialize-google-insight-row > div:first-child {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.72rem;
      }

      .goosialize-google-insight-track {
        height: 0.28rem;
        margin-top: 0.3rem;
        overflow: hidden;
        border-radius: 999px;
        background:
          color-mix(
            in srgb,
            var(--muted-foreground)
            12%,
            transparent
          );
      }

      .goosialize-google-insight-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--primary);
      }

      .goosialize-google-insight-row small {
        display: block;
        margin-top: 0.18rem;
        font-size: 0.62rem;
        color: var(--muted-foreground);
      }

      .goosialize-google-section-intro h3 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
      }

      .goosialize-google-section-intro p {
        margin: 0.15rem 0 0;
        font-size: 0.7rem;
        color: var(--muted-foreground);
      }

      .goosialize-google-links-chart {
        min-height: 270px;
        padding: 1rem 1.1rem;
        border: 1px solid var(--border);
        background: var(--card);
        border-radius: 0.5rem;
      }

      .goosialize-google-links-chart-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.5rem;
      }

      .goosialize-google-links-chart-header h3 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
      }

      .goosialize-google-links-chart-header p {
        margin: 0.125rem 0 0;
        font-size: 0.6875rem;
        color: var(--muted-foreground);
      }

      .goosialize-google-chart-toggles {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.35rem;
      }

      .goosialize-google-chart-toggle {
        height: 1.8rem;
        padding: 0 0.55rem;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: transparent;
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        transition:
          opacity 120ms ease,
          border-color 120ms ease,
          color 120ms ease,
          background 120ms ease;
      }

      .goosialize-google-chart-toggle:hover {
        background:
          color-mix(
            in srgb,
            var(--foreground)
            5%,
            transparent
          );
      }

      .goosialize-google-chart-legend {
        display: flex;
        gap: 0.75rem;
        font-size: 0.65rem;
        color: var(--muted-foreground);
      }

      .goosialize-google-chart-legend span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
      }

      .goosialize-google-chart-legend i {
        display: inline-block;
        width: 1rem;
        height: 2px;
      }

      .goosialize-google-links-chart-shell {
        position: relative;
        height: 230px;
      }

      .goosialize-google-links-chart-shell svg {
        width: 100%;
        height: 100%;
        display: block;
      }

      .goosialize-google-chart-tooltip {
        position: absolute;
        display: none;
        z-index: 20;
        min-width: 9rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--border);
        background: var(--popover);
        border-radius: 0.4rem;
        box-shadow:
          0 8px 24px
          rgba(0,0,0,.18);
        font-size: 0.65rem;
        pointer-events: none;
      }

      .goosialize-google-chart-tooltip strong {
        display: block;
        margin-bottom: 0.35rem;
      }

      .goosialize-google-chart-tooltip div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.08rem 0;
      }

      .goosialize-google-geo-body {
        display: grid;
        grid-template-columns:
          minmax(0, 1fr);
        align-items: start;
        gap: 0.75rem;
      }

      @media (min-width: 1100px) {
        .goosialize-google-geo-body {
          grid-template-columns:
            minmax(0, 2fr)
            minmax(220px, 0.7fr);
        }
      }
    `;

    document.head.append(style);
  }

  class GoosializeGooglePage
    extends HTMLElement {
    constructor() {
      super();

      this.product = 'analytics';
      this.properties = [];
      this.propertyId = '';
      this.days = 30;
      this.data = null;
      this.loading = false;
      this.loadingProgress = 0;
      this.loadingPreviousProgress = 0;
      this.loadingStatus = '';
      this.error = null;

    }

    connectedCallback() {
      ensureDashboardStyles();
      this.render();
      this.load();
    }

    disconnectedCallback() {
    }

    async apiGet(path) {
      const response =
        await fetch(
          apiUrl(path),
          {
            method: 'GET',
            headers: apiHeaders(),
            credentials: 'same-origin',
            cache: 'no-store',
          }
        );

      let payload = null;

      try {
        payload =
          await response.json();
      } catch {
        payload = null;
      }

      if (!response.ok) {
        const error =
          new Error(
            payload?.message
            || `Request failed with HTTP ${response.status}`
          );

        error.status =
          response.status;

        error.code =
          payload?.code
          ?? null;

        throw error;
      }

      return payload;
    }

    async load() {
      this.loading = true;
      this.loadingPreviousProgress = 0;
      this.loadingProgress = 0;
      this.loadingStatus =
        this.product === 'analytics'
          ? 'Loading available properties…'
          : 'Loading Search Console…';
      this.error = null;
      this.render();

      try {
        const propertiesPath =
          this.product === 'search_console'
            ? '/goosialize-google/search-console/properties'
            : '/goosialize-google/properties';

        const payload =
          await this.apiGet(
            propertiesPath
          );

        this.properties =
          Array.isArray(payload?.data)
            ? payload.data
            : [];

        if (
          this.product === 'analytics'
        ) {
          this.loadingPreviousProgress =
            this.loadingProgress;
          this.loadingProgress = 5;
          this.loadingStatus =
            'Preparing analytics dashboard…';
          this.render();
        } else if (
          this.product === 'search_console'
        ) {
          this.loadingPreviousProgress =
            this.loadingProgress;
          this.loadingProgress = 10;
          this.loadingStatus =
            'Preparing Search Console dashboard…';
          this.render();
        }

        if (
          this.propertyId === ''
          && this.properties.length > 0
        ) {
          this.propertyId =
            this.product === 'search_console'
              ? String(
                  this.properties[0]
                    .site_url
                  || ''
                )
              : String(
                  this.properties[0]
                    .property_id
                  || ''
                );
        }

        if (this.propertyId) {
          await this.loadDashboard(
            false
          );
        } else {
          this.data = null;
        }
      } catch (error) {
        this.error =
          error instanceof Error
            ? error.message
            : this.product
                === 'search_console'
              ? 'Google Search Console could not be loaded.'
              : 'Google Analytics could not be loaded.';
      } finally {
        this.loading = false;
        this.render();
      }
    }

    async loadDashboard(
      manageState = true
    ) {
      if (this.propertyId === '') {
        this.data = null;
        this.render();
        return;
      }

      if (manageState) {
        this.loading = true;
        this.loadingPreviousProgress = 0;
        this.loadingProgress =
          this.product === 'analytics'
            ? 5
            : 0;
        this.loadingStatus =
          this.product === 'analytics'
            ? 'Preparing analytics dashboard…'
            : 'Loading Search Console…';
        this.error = null;
        this.render();
      }

      try {
        const query =
          new URLSearchParams();

        if (
          this.product
          === 'search_console'
        ) {
          query.set(
            'site_url',
            this.propertyId
          );
        } else {
          query.set(
            'property_id',
            this.propertyId
          );
        }

        query.set(
          'days',
          String(this.days)
        );

        if (
          this.product === 'analytics'
        ) {
          const packages = [
            {
              id: 'overview',
              progress: 15,
              status:
                'Loading overview metrics…',
            },
            {
              id: 'trend',
              progress: 30,
              status:
                'Loading visitor trends…',
            },
            {
              id: 'top_pages',
              progress: 40,
              status:
                'Loading top pages…',
            },
            {
              id: 'audience',
              progress: 55,
              status:
                'Loading traffic and devices…',
            },
            {
              id: 'geo',
              progress: 70,
              status:
                'Loading visitor locations…',
            },
            {
              id: 'timing',
              progress: 82,
              status:
                'Loading busiest days and hours…',
            },
            {
              id: 'events',
              progress: 92,
              status:
                'Loading events…',
            },
            {
              id: 'demographics',
              progress: 97,
              status:
                'Loading optional demographics…',
            },
          ];

          this.data = {};

          for (
            const item
            of packages
          ) {
            this.loadingStatus =
              item.status;

            this.render();

            const packageQuery =
              new URLSearchParams(
                query
              );

            packageQuery.set(
              'package',
              item.id
            );

            const payload =
              await this.apiGet(
                `/goosialize-google/analytics?${packageQuery.toString()}`
              );

            const {
              ok,
              package:
                loadedPackage,
              ...packageData
            } = payload || {};

            this.data = {
              ...(this.data || {}),
              ...packageData,
            };

            this.loadingPreviousProgress =
              this.loadingProgress;

            this.loadingProgress =
              item.progress;

            this.render();

            if (
              item.id === 'overview'
              && packageData.empty === true
            ) {
              this.data = {
                ...(this.data || {}),
                empty: true,
              };

              this.loadingPreviousProgress =
                this.loadingProgress;

              this.loadingProgress = 100;
              this.loadingStatus =
                'No analytics data available';

              this.render();

              await new Promise(
                (resolve) =>
                  window.setTimeout(
                    resolve,
                    320
                  )
              );

              break;
            }
          }

          if (
            this.data?.empty !== true
          ) {
            this.data = {
            ...(this.data || {}),
            empty:
              Number(
                this.data
                  ?.overview
                  ?.activeUsers
                || 0
              ) === 0
              && (
                !Array.isArray(
                  this.data?.top_pages
                )
                || this.data
                    .top_pages
                    .length === 0
              )
              && (
                !Array.isArray(
                  this.data
                    ?.traffic_channels
                )
                || this.data
                    .traffic_channels
                    .length === 0
              ),
            };

            this.loadingPreviousProgress =
              this.loadingProgress;

            this.loadingProgress = 100;
            this.loadingStatus =
              'Dashboard ready';
          }

          this.render();

          await new Promise(
            (resolve) =>
              window.setTimeout(
                resolve,
                220
              )
          );
        } else {
          const packages = [
            {
              id: 'overview',
              progress: 25,
              status:
                'Loading search overview…',
            },
            {
              id: 'trend',
              progress: 50,
              status:
                'Loading daily search performance…',
            },
            {
              id: 'content',
              progress: 75,
              status:
                'Loading queries and pages…',
            },
            {
              id: 'breakdowns',
              progress: 95,
              status:
                'Loading devices and countries…',
            },
          ];

          this.data = {};

          for (
            const item
            of packages
          ) {
            this.loadingStatus =
              item.status;

            this.render();

            const packageQuery =
              new URLSearchParams(
                query
              );

            packageQuery.set(
              'package',
              item.id
            );

            const payload =
              await this.apiGet(
                `/goosialize-google/search-console/performance?${packageQuery.toString()}`
              );

            const {
              ok,
              package:
                loadedPackage,
              ...packageData
            } = payload || {};

            this.data = {
              ...(this.data || {}),
              ...packageData,
            };

            this.loadingPreviousProgress =
              this.loadingProgress;

            this.loadingProgress =
              item.progress;

            this.render();

            if (
              item.id === 'overview'
              && packageData.empty === true
            ) {
              this.data = {
                ...(this.data || {}),
                empty: true,
              };

              this.loadingPreviousProgress =
                this.loadingProgress;

              this.loadingProgress = 100;
              this.loadingStatus =
                'No Search Console data available';

              this.render();

              await new Promise(
                (resolve) =>
                  window.setTimeout(
                    resolve,
                    320
                  )
              );

              break;
            }
          }

          if (
            this.data?.empty !== true
          ) {
            this.data = {
              ...(this.data || {}),
              empty: false,
            };

            this.loadingPreviousProgress =
              this.loadingProgress;

            this.loadingProgress = 100;
            this.loadingStatus =
              'Dashboard ready';

            this.render();

            await new Promise(
              (resolve) =>
                window.setTimeout(
                  resolve,
                  220
                )
            );
          }
        }
      } catch (error) {
        this.data = null;

        this.error =
          error instanceof Error
            ? error.message
            : this.product
                === 'search_console'
              ? 'Google Search Console could not be loaded.'
              : 'Google Analytics could not be loaded.';
      } finally {
        if (manageState) {
          this.loading = false;
          this.loadingStatus = '';
          this.render();
        }
      }
    }

    render() {
      this.replaceChildren();

      const root =
        element('section');

      root.className =
        'space-y-4 px-6 pb-6';

      const productNav =
        element('div');

      productNav.className =
        'flex gap-1 overflow-x-auto border-b border-border [scrollbar-width:none] [&::-webkit-scrollbar]:hidden';

      productNav.setAttribute(
        'aria-label',
        'Google product'
      );

      for (
        const [id, label]
        of [
          ['analytics', 'Google Analytics'],
          ['search_console', 'Search Console'],
        ]
      ) {
        const active =
          this.product === id;

        const button =
          element(
            'button',
            label
          );

        button.type = 'button';

        button.disabled =
          this.loading;

        button.className =
          active
            ? 'flex shrink-0 items-center whitespace-nowrap border-b-2 border-primary px-3 py-2 text-sm font-medium text-primary'
            : 'flex shrink-0 items-center whitespace-nowrap border-b-2 border-transparent px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:border-border hover:text-foreground';

        button.setAttribute(
          'aria-pressed',
          active
            ? 'true'
            : 'false'
        );

        button.addEventListener(
          'click',
          () => {
            if (
              this.product === id
              || this.loading
            ) {
              return;
            }

            this.product = id;
            this.properties = [];
            this.propertyId = '';
            this.data = null;
            this.error = null;

            this.load();
          }
        );

        productNav.append(button);
      }

      root.append(productNav);

      const toolbar =
        element('section');

      toolbar.className =
        'flex flex-col gap-3 sm:flex-row sm:items-end';

      const propertyGroup =
        element('label');

      propertyGroup.className =
        'min-w-0 flex-1';

      const propertyLabel =
        element(
          'span',
          this.product === 'search_console'
            ? 'Search Console property'
            : 'Property'
        );

      propertyLabel.className =
        'block text-xs font-medium text-muted-foreground';

      const property =
        element('select');

      property.className =
        'mt-1 h-10 w-full rounded-lg border border-input bg-muted/50 px-3 py-2 text-sm text-foreground shadow-sm focus:outline-none focus:ring-1 focus:ring-ring';

      property.setAttribute(
        'aria-label',
        this.product === 'search_console'
          ? 'Search Console property'
          : 'GA4 property'
      );

      property.disabled =
        this.loading;

      if (
        this.properties.length === 0
      ) {
        const option =
          element(
            'option',
            this.product === 'search_console'
              ? 'No accessible Search Console properties'
              : 'No accessible properties'
          );

        option.value = '';
        property.append(option);
        property.disabled = true;
      } else {
        for (
          const item
          of this.properties
        ) {
          const optionLabel =
            this.product === 'search_console'
              ? String(
                  item.site_url
                  || ''
                )
              : `${item.property_name} — ${item.account_name}`;

          const optionValue =
            this.product === 'search_console'
              ? String(
                  item.site_url
                  || ''
                )
              : String(
                  item.property_id
                  || ''
                );

          const option =
            element(
              'option',
              optionLabel
            );

          option.value =
            optionValue;

          option.selected =
            option.value
              === this.propertyId;

          property.append(option);
        }
      }

      property.addEventListener(
        'change',
        () => {
          this.propertyId =
            property.value;

          this.loadDashboard();
        }
      );

      propertyGroup.append(
        propertyLabel,
        property
      );

      const periodGroup =
        element('div');

      periodGroup.className =
        'shrink-0';

      const periodLabel =
        element(
          'div',
          'Period'
        );

      periodLabel.className =
        'text-xs font-medium text-muted-foreground';

      const periodControls =
        element('div');

      periodControls.className =
        'inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm';

      periodControls.setAttribute(
        'role',
        'group'
      );

      periodControls.setAttribute(
        'aria-label',
        this.product === 'search_console'
          ? 'Search Console period'
          : 'Analytics period'
      );

      for (
        const days
        of [7, 30, 90]
      ) {
        const active =
          days === this.days;

        const button =
          element(
            'button',
            `${days} days`
          );

        button.type =
          'button';

        button.disabled =
          this.loading;

        button.className =
          active
            ? 'h-8 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground'
            : 'h-8 rounded-md px-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground';

        button.setAttribute(
          'aria-pressed',
          active
            ? 'true'
            : 'false'
        );

        button.addEventListener(
          'click',
          () => {
            if (
              days === this.days
              || this.loading
            ) {
              return;
            }

            this.days = days;

            this.loadDashboard();
          }
        );

        periodControls.append(
          button
        );
      }

      periodGroup.append(
        periodLabel,
        periodControls
      );

      const refreshButton =
        element('button');

      refreshButton.type =
        'button';

      refreshButton.disabled =
        this.loading
        || this.propertyId === '';

      refreshButton.className =
        'goosialize-google-refresh-button';

      refreshButton.setAttribute(
        'aria-label',
        this.loading
          ? 'Refreshing data'
          : 'Refresh data'
      );

      refreshButton.title =
        this.loading
          ? 'Refreshing data…'
          : 'Refresh data';

      const refreshIcon =
        element('span', '↻');

      refreshIcon.className =
        'goosialize-google-refresh-icon';

      refreshIcon.setAttribute(
        'aria-hidden',
        'true'
      );

      refreshButton.append(
        refreshIcon
      );

      if (this.loading) {
        refreshButton.classList.add(
          'is-loading'
        );
      }

      refreshButton.addEventListener(
        'click',
        () => {
          if (
            this.loading
            || this.propertyId === ''
          ) {
            return;
          }

          this.loadDashboard();
        }
      );

      refreshButton.classList.add(
        'goosialize-google-period-refresh'
      );

      const periodRow =
        element('div');

      periodRow.className =
        'goosialize-google-period-row mt-1';

      periodRow.append(
        periodControls,
        refreshButton
      );

      periodGroup.replaceChildren(
        periodLabel,
        periodRow
      );

      toolbar.append(
        propertyGroup,
        periodGroup
      );

      root.append(toolbar);

      if (this.loading) {
        const progressPanel =
          element('section');

        progressPanel.className =
          'goosialize-google-loading-panel';

        progressPanel.setAttribute(
          'aria-live',
          'polite'
        );

        const progressHeader =
          element('div');

        progressHeader.className =
          'goosialize-google-loading-header';

        const status =
          element(
            'span',
            this.loadingStatus
            || (
              this.product
                === 'search_console'
                ? 'Loading Search Console…'
                : 'Loading Google Analytics…'
            )
          );

        const percentage =
          element(
            'strong',
            (
              this.product === 'analytics'
              || this.product === 'search_console'
            )
              ? `${Math.round(
                  this.loadingProgress
                )}% complete`
              : ''
          );

        progressHeader.append(
          status,
          percentage
        );

        const progress =
          element('div');

        progress.className =
          'goosialize-google-loading-track';

        progress.setAttribute(
          'role',
          'progressbar'
        );

        progress.setAttribute(
          'aria-busy',
          'true'
        );

        progress.setAttribute(
          'aria-label',
          this.product === 'search_console'
            ? 'Loading Search Console data'
            : 'Loading Google Analytics data'
        );

        const progressBar =
          element('div');

        progressBar.className =
          'goosialize-google-loading-value';

        if (
          this.product === 'analytics'
          || this.product === 'search_console'
        ) {
          progress.setAttribute(
            'aria-valuemin',
            '0'
          );

          progress.setAttribute(
            'aria-valuemax',
            '100'
          );

          progress.setAttribute(
            'aria-valuenow',
            String(
              Math.round(
                this.loadingProgress
              )
            )
          );

          const fromProgress =
            Math.max(
              0,
              Math.min(
                100,
                Number(
                  this.loadingPreviousProgress
                  || 0
                )
              )
            );

          const toProgress =
            Math.max(
              0,
              Math.min(
                100,
                Number(
                  this.loadingProgress
                  || 0
                )
              )
            );

          progressBar.style.width =
            `${toProgress}%`;

          if (
            toProgress !== fromProgress
          ) {
            progressBar.animate(
              [
                {
                  width:
                    `${fromProgress}%`,
                },
                {
                  width:
                    `${toProgress}%`,
                },
              ],
              {
                duration: 520,
                easing:
                  'cubic-bezier(0.22, 1, 0.36, 1)',
                fill: 'both',
              }
            );
          }
        } else {
          progressBar.classList.add(
            'is-indeterminate'
          );
        }

        progress.append(
          progressBar
        );

        const hint =
          element(
            'small',
            this.product === 'analytics'
              ? 'Loading live GA4 data. You can safely wait on this page.'
              : 'Loading live Search Console data…'
          );

        progressPanel.append(
          progressHeader,
          progress,
          hint
        );

        root.append(
          progressPanel
        );

        this.append(root);
        return;
      }

      if (this.error) {
        const panel =
          element('section');

        panel.className =
          'rounded-md border border-destructive/40 bg-destructive/10 p-4 text-sm';

        panel.setAttribute(
          'role',
          'alert'
        );

        const title =
          element(
            'strong',
            this.product === 'search_console'
              ? 'Google Search Console could not be loaded'
              : 'Google Analytics could not be loaded'
          );

        title.className =
          'block font-semibold text-destructive';

        const message =
          element(
            'p',
            this.error
          );

        message.className =
          'mt-1 text-sm text-muted-foreground';

        panel.append(
          title,
          message
        );

        root.append(panel);
        this.append(root);
        return;
      }

      if (
        this.properties.length === 0
      ) {
        const panel =
          element('section');

        panel.className =
          'rounded-lg border border-border bg-card p-5';

        const title =
          element(
            'strong',
            this.product === 'search_console'
              ? 'No accessible Search Console properties'
              : 'No accessible Google Analytics properties'
          );

        title.className =
          'font-semibold';

        const copy =
          element(
            'p',
            this.product === 'search_console'
              ? 'The configured Google account does not currently expose any Search Console properties.'
              : 'The configured Google account does not currently expose any GA4 properties.'
          );

        copy.className =
          'mt-1 text-sm text-muted-foreground';

        panel.append(
          title,
          copy
        );

        root.append(panel);
        this.append(root);
        return;
      }

      if (!this.data) {
        this.append(root);
        return;
      }

      const heading =
        element('header');

      heading.className =
        'space-y-0.5 pt-1';

      const title =
        element(
          'h2',
          this.product === 'search_console'
            ? (
                this.data?.property?.site_url
                || 'Google Search Console'
              )
            : (
                this.data?.property?.name
                || 'Google Analytics'
              )
        );

      title.className =
        'text-xl font-semibold tracking-tight text-foreground';

      const period =
        element(
          'p',
          `${this.data?.period?.start_date || ''} — ${this.data?.period?.end_date || ''}`
        );

      period.className =
        'text-sm text-muted-foreground';

      heading.append(
        title,
        period
      );

      root.append(heading);

      if (
        this.data.empty === true
      ) {
        const panel =
          element('section');

        panel.className =
          'rounded-lg border border-border bg-card py-12 text-center';

        const title =
          element(
            'h3',
            this.product === 'search_console'
              ? 'No Search Console data'
              : 'No analytics data'
          );

        title.className =
          'text-sm font-medium text-foreground';

        const copy =
          element(
            'p',
            this.product === 'search_console'
              ? 'No Search Console data for this period.'
              : 'No analytics data for this period.'
          );

        copy.className =
          'mt-2 text-sm text-muted-foreground';

        const help =
          element(
            'p',
            this.product === 'search_console'
              ? 'Try another period or confirm that this property has search performance data.'
              : 'Try another period or confirm that this GA4 property is receiving traffic.'
          );

        help.className =
          'mt-1 text-sm text-muted-foreground';

        panel.append(
          title,
          copy,
          help
        );

        root.append(panel);
        this.append(root);
        return;
      }

      if (
        this.product === 'analytics'
      ) {
        this.appendAnalyticsTrendChart(
          root,
          this.data.trend
        );
      } else if (
        this.product === 'search_console'
      ) {
        this.appendSearchConsoleTrendChart(
          root,
          this.data.trend
        );
      }

      const metrics =
        this.data.overview
        || {};

      const cards =
        element('section');

      cards.className =
        'grid gap-4 sm:grid-cols-2 xl:grid-cols-4';

      cards.setAttribute(
        'aria-label',
        this.product === 'search_console'
          ? 'Search Console overview'
          : 'Analytics overview'
      );

      const definitions =
        this.product === 'search_console'
          ? [
              [
                'Clicks',
                formatInteger(
                  metrics.clicks
                ),
                'fa-arrow-pointer',
                'violet',
                '↗',
              ],
              [
                'Impressions',
                formatInteger(
                  metrics.impressions
                ),
                'fa-eye',
                'blue',
                '◉',
              ],
              [
                'CTR',
                formatPercent(
                  metrics.ctr
                ),
                'fa-percent',
                'teal',
                '%',
              ],
              [
                'Average position',
                formatDecimal(
                  metrics.position,
                  1
                ),
                'fa-ranking-star',
                'amber',
                '#',
              ],
            ]
          : [
              [
                'Active users',
                formatInteger(
                  metrics.activeUsers
                ),
                'fa-users',
                'violet',
                '●',
              ],
              [
                'New users',
                formatInteger(
                  metrics.newUsers
                ),
                'fa-user-plus',
                'violet',
                '+',
              ],
              [
                'Sessions',
                formatInteger(
                  metrics.sessions
                ),
                'fa-rotate',
                'blue',
                '↻',
              ],
              [
                'Views',
                formatInteger(
                  metrics.screenPageViews
                ),
                'fa-eye',
                'cyan',
                '◉',
              ],
              [
                'Engagement',
                formatPercent(
                  metrics.engagementRate
                ),
                'fa-bullseye',
                'green',
                '◆',
              ],
              [
                'Avg. session',
                formatDuration(
                  metrics.averageSessionDuration
                ),
                'fa-clock',
                'amber',
                '◷',
              ],
              [
                'Events',
                formatInteger(
                  metrics.eventCount
                ),
                'fa-bolt',
                'pink',
                'ϟ',
              ],
              [
                'Key events',
                formatInteger(
                  metrics.keyEvents
                ),
                'fa-star',
                'orange',
                '★',
              ],
            ];

      cards.replaceChildren();

      cards.className =
        'goosialize-google-kpi-grid';

      cards.style.display = '';
      cards.style.gridTemplateColumns = '';
      cards.style.gap = '';

      for (
        const [
          label,
          value,
          iconClass,
          tone,
          fallback,
        ]
        of definitions
      ) {
        cards.append(
          googleMetricCard(
            label,
            value,
            iconClass,
            tone,
            fallback
          )
        );
      }

      for (
        const card
        of Array.from(
          cards.children
        )
      ) {
        const raw =
          String(
            card.textContent
            || ''
          );

        const label =
          Object.keys(
            GOOGLE_METRIC_HELP
          ).find(
            (candidate) =>
              raw.includes(candidate)
          );

        if (!label) {
          continue;
        }

        card.style.position =
          'relative';

        const info =
          googleInfoIcon(
            googleMetricHelp(label),
            `${label} information`
          );

        info.classList.add(
          'goosialize-google-kpi-info'
        );

        card.append(info);
      }

      root.append(cards);

      if (
        this.product === 'search_console'
        && this.data?.empty !== true
      ) {
        this.appendSearchConsoleHighlights(
          root
        );
      }

      if (
        this.product === 'analytics'
      ) {
        this.appendTable(
          root,
          'Top pages',
          this.data.top_pages,
          [['pagePath', 'Page']],
          [
            ['screenPageViews', 'Views'],
            ['activeUsers', 'Users'],
          ]
        );
      }

      const tables =
        element('section');

      tables.className =
        'goosialize-google-detail-grid';

      if (
        this.product === 'search_console'
      ) {
        const searchMetrics = [
          [
            'clicks',
            'Clicks',
            formatInteger,
          ],
          [
            'impressions',
            'Impressions',
            formatInteger,
          ],
          [
            'ctr',
            'CTR',
            formatPercent,
          ],
          [
            'position',
            'Position',
            (value) =>
              formatDecimal(
                value,
                1
              ),
          ],
        ];

        const searchInsights =
          element('section');

        searchInsights.className =
          'goosialize-google-admin-insight-grid';

        this.appendSearchConsoleInsight(
          searchInsights,
          'Top queries',
          this.data.top_queries,
          'query'
        );

        this.appendSearchConsoleInsight(
          searchInsights,
          'Search devices',
          this.data.devices,
          'device'
        );

        this.appendSearchConsoleInsight(
          searchInsights,
          'Search countries',
          this.data.countries,
          'country'
        );

        if (
          searchInsights.childElementCount > 0
        ) {
          root.append(
            searchInsights
          );
        }

        this.appendSearchConsoleTable(
          root,
          'Search pages',
          this.data.top_pages,
          [['page', 'Page']],
          searchMetrics
        );
      } else {
        const audience =
          element('section');

        audience.className =
          'goosialize-google-admin-insight-grid';

        this.appendAdminInsight(
          audience,
          'Traffic channels',
          this.data.traffic_channels,
          'sessionDefaultChannelGroup',
          'sessions',
          'Sessions'
        );

        this.appendAdminInsight(
          audience,
          'Devices',
          this.data.devices,
          'deviceCategory',
          'sessions',
          'Sessions'
        );

        this.appendAdminInsight(
          audience,
          'Operating systems',
          this.data.operating_systems,
          'operatingSystem',
          'sessions',
          'Sessions'
        );

        this.appendAdminInsight(
          audience,
          'Top countries',
          this.data.countries,
          'country',
          'activeUsers',
          'Users'
        );

        if (
          audience.childElementCount > 0
        ) {
          root.append(audience);
        }

        this.appendGeoMap(
          root,
          this.data.countries,
          this.data.cities
        );

        const timing =
          element('section');

        timing.className =
          'goosialize-google-admin-insight-grid goosialize-google-timing-grid';

        this.appendBusiestDaysBars(
          timing,
          this.data.busy_days
        );

        this.appendBusiestHoursDonut(
          timing,
          this.data.busy_hours
        );

        if (
          timing.childElementCount > 0
        ) {
          root.append(timing);
        }

        const demographics =
          element('section');

        demographics.className =
          'goosialize-google-admin-insight-grid';

        this.appendAdminInsight(
          demographics,
          'Gender',
          this.data.gender,
          'userGender',
          'activeUsers',
          'Users'
        );

        this.appendAdminInsight(
          demographics,
          'Age',
          this.data.ages,
          'userAgeBracket',
          'activeUsers',
          'Users'
        );

        if (
          demographics.childElementCount > 0
        ) {
          const demoTitle =
            element('div');

          demoTitle.className =
            'goosialize-google-section-intro';

          demoTitle.append(
            element(
              'h3',
              'Audience demographics'
            ),
            element(
              'p',
              'Shown only when Google Analytics makes demographic data available.'
            )
          );

          root.append(
            demoTitle,
            demographics
          );
        }

        this.appendTable(
          root,
          'Events',
          this.data.events,
          [['eventName', 'Event']],
          [
            ['eventCount', 'Count'],
            ['totalUsers', 'Users'],
          ]
        );
      }

      if (
        tables.childElementCount > 0
      ) {
        root.append(tables);
      }

      this.append(root);
    }

    appendSearchConsoleHighlights(
      root
    ) {
      const highlights = [];

      const query =
        Array.isArray(
          this.data?.top_queries
        )
          ? this.data.top_queries[0]
          : null;

      const page =
        Array.isArray(
          this.data?.top_pages
        )
          ? this.data.top_pages[0]
          : null;

      const device =
        Array.isArray(
          this.data?.devices
        )
          ? this.data.devices[0]
          : null;

      const country =
        Array.isArray(
          this.data?.countries
        )
          ? this.data.countries[0]
          : null;

      if (
        query?.query
      ) {
        highlights.push({
          title: 'Top query',
          value:
            String(query.query),
          meta:
            `${formatInteger(
              query.clicks || 0
            )} clicks · ${formatInteger(
              query.impressions || 0
            )} impressions`,
        });
      }

      if (
        page?.page
      ) {
        highlights.push({
          title: 'Best page',
          value:
            String(page.page),
          meta:
            `${formatInteger(
              page.clicks || 0
            )} clicks · ${formatInteger(
              page.impressions || 0
            )} impressions`,
        });
      }

      if (
        device?.device
      ) {
        highlights.push({
          title: 'Top device',
          value:
            String(device.device),
          meta:
            `${formatInteger(
              device.impressions || 0
            )} impressions`,
        });
      }

      if (
        country?.country
      ) {
        highlights.push({
          title: 'Top country',
          value:
            String(country.country),
          meta:
            `${formatInteger(
              country.impressions || 0
            )} impressions`,
        });
      }

      if (
        highlights.length === 0
      ) {
        return;
      }

      const grid =
        element('section');

      grid.className =
        'goosialize-google-search-highlights';

      for (
        const item
        of highlights
      ) {
        const card =
          element('article');

        card.className =
          'goosialize-google-search-highlight';

        card.append(
          element(
            'small',
            item.title
          ),
          element(
            'strong',
            item.value
          ),
          element(
            'span',
            item.meta
          )
        );

        grid.append(card);
      }

      root.append(grid);
    }

    appendSearchConsoleTrendChart(
      root,
      rows
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const data =
        rows
          .map((row) => ({
            date:
              String(
                row?.date
                || ''
              ),
            clicks:
              Number(
                row?.clicks
                || 0
              ),
            impressions:
              Number(
                row?.impressions
                || 0
              ),
            ctr:
              Number(
                row?.ctr
                || 0
              ),
            position:
              Number(
                row?.position
                || 0
              ),
          }))
          .filter(
            (row) =>
              row.date !== ''
          )
          .sort(
            (a, b) =>
              a.date.localeCompare(
                b.date
              )
          );

      if (data.length === 0) {
        return;
      }

      const wrapper =
        element('section');

      wrapper.className =
        'goosialize-google-links-chart';

      const header =
        element('div');

      header.className =
        'goosialize-google-links-chart-header';

      const headingBlock =
        element('div');

      const heading =
        element(
          'h3',
          'Search performance over time'
        );

      const period =
        element(
          'p',
          `${formatGaDate(data[0].date)} — ${formatGaDate(data[data.length - 1].date)}`
        );

      headingBlock.append(
        heading,
        period
      );

      const series = [
        {
          key: 'clicks',
          label: 'Clicks',
          color: '#8b5cf6',
          format: formatInteger,
          enabled: true,
        },
        {
          key: 'impressions',
          label: 'Impressions',
          color: '#3b82f6',
          format: formatInteger,
          enabled: true,
        },
        {
          key: 'ctr',
          label: 'CTR',
          color: '#14b8a6',
          format: formatPercent,
          enabled: false,
        },
        {
          key: 'position',
          label: 'Position',
          color: '#f59e0b',
          format:
            (value) =>
              formatDecimal(
                value,
                1
              ),
          enabled: false,
        },
      ];

      const active =
        new Set(
          series
            .filter(
              (item) =>
                item.enabled
            )
            .map(
              (item) =>
                item.key
            )
        );

      const toggles =
        element('div');

      toggles.className =
        'goosialize-google-chart-toggles';

      header.append(
        headingBlock,
        toggles
      );

      const chartShell =
        element('div');

      chartShell.className =
        'goosialize-google-links-chart-shell';

      wrapper.append(
        header,
        chartShell
      );

      const renderChart =
        () => {
          chartShell.replaceChildren();

          const ns =
            'http://www.w3.org/2000/svg';

          const svg =
            document.createElementNS(
              ns,
              'svg'
            );

          const width = 900;
          const height = 280;
          const left = 38;
          const right = 16;
          const top = 18;
          const bottom = 34;

          svg.setAttribute(
            'viewBox',
            `0 0 ${width} ${height}`
          );

          svg.setAttribute(
            'preserveAspectRatio',
            'xMidYMid meet'
          );

          svg.style.width =
            '100%';

          svg.style.height =
            '100%';

          svg.style.display =
            'block';

          const plotWidth =
            width - left - right;

          const plotHeight =
            height - top - bottom;

          const xFor =
            (index) =>
              left
              + (
                  data.length === 1
                    ? plotWidth / 2
                    : (
                        plotWidth
                        * index
                        / (
                          data.length - 1
                        )
                      )
                );

          const maxima = {};

          for (const item of series) {
            maxima[item.key] =
              Math.max(
                1,
                ...data.map(
                  (row) =>
                    Number(
                      row[item.key]
                      || 0
                    )
                )
              );
          }

          const yFor =
            (
              key,
              value
            ) =>
              top
              + plotHeight
              - (
                  Number(value || 0)
                  / maxima[key]
                  * plotHeight
                );

          for (
            let tick = 0;
            tick <= 4;
            tick += 1
          ) {
            const y =
              top
              + (
                  plotHeight
                  * tick
                  / 4
                );

            const line =
              document.createElementNS(
                ns,
                'line'
              );

            line.setAttribute(
              'x1',
              String(left)
            );

            line.setAttribute(
              'x2',
              String(
                width - right
              )
            );

            line.setAttribute(
              'y1',
              String(y)
            );

            line.setAttribute(
              'y2',
              String(y)
            );

            line.setAttribute(
              'stroke',
              'currentColor'
            );

            line.setAttribute(
              'stroke-opacity',
              '0.08'
            );

            line.setAttribute(
              'stroke-dasharray',
              '3 3'
            );

            svg.append(line);
          }

          const curve =
            (points) =>
              points
                .map(
                  (
                    point,
                    index
                  ) => {
                    if (index === 0) {
                      return (
                        `M ${point.x},${point.y}`
                      );
                    }

                    const previous =
                      points[index - 1];

                    const control =
                      (
                        previous.x
                        + point.x
                      ) / 2;

                    return (
                      `C ${control},${previous.y} ` +
                      `${control},${point.y} ` +
                      `${point.x},${point.y}`
                    );
                  }
                )
                .join(' ');

          for (const item of series) {
            if (
              !active.has(
                item.key
              )
            ) {
              continue;
            }

            const points =
              data.map(
                (
                  row,
                  index
                ) => ({
                  x:
                    xFor(index),
                  y:
                    yFor(
                      item.key,
                      row[item.key]
                    ),
                })
              );

            const path =
              document.createElementNS(
                ns,
                'path'
              );

            path.setAttribute(
              'd',
              curve(points)
            );

            path.setAttribute(
              'fill',
              'none'
            );

            path.setAttribute(
              'stroke',
              item.color
            );

            path.setAttribute(
              'stroke-width',
              '2'
            );

            path.setAttribute(
              'vector-effect',
              'non-scaling-stroke'
            );

            svg.append(path);

            points.forEach(
              (point) => {
                const marker =
                  document.createElementNS(
                    ns,
                    'circle'
                  );

                marker.setAttribute(
                  'cx',
                  String(point.x)
                );

                marker.setAttribute(
                  'cy',
                  String(point.y)
                );

                marker.setAttribute(
                  'r',
                  '4'
                );

                marker.setAttribute(
                  'fill',
                  item.color
                );

                marker.setAttribute(
                  'stroke',
                  item.color
                );

                marker.setAttribute(
                  'stroke-width',
                  '1.5'
                );

                svg.append(marker);
              }
            );
          }

          const tooltip =
            element('div');

          tooltip.className =
            'goosialize-google-chart-tooltip';

          data.forEach(
            (
              row,
              index
            ) => {
              const x =
                xFor(index);

              const hit =
                document.createElementNS(
                  ns,
                  'rect'
                );

              const previousX =
                index === 0
                  ? left
                  : (
                      xFor(index - 1)
                      + x
                    ) / 2;

              const nextX =
                index ===
                  data.length - 1
                  ? width - right
                  : (
                      x
                      + xFor(index + 1)
                    ) / 2;

              hit.setAttribute(
                'x',
                String(previousX)
              );

              hit.setAttribute(
                'y',
                String(top)
              );

              hit.setAttribute(
                'width',
                String(
                  Math.max(
                    1,
                    nextX - previousX
                  )
                )
              );

              hit.setAttribute(
                'height',
                String(plotHeight)
              );

              hit.setAttribute(
                'fill',
                'transparent'
              );

              hit.style.cursor =
                'crosshair';

              hit.addEventListener(
                'mouseenter',
                () => {
                  tooltip.replaceChildren();

                  tooltip.append(
                    element(
                      'strong',
                      formatGaDate(
                        row.date
                      )
                    )
                  );

                  for (
                    const item
                    of series
                  ) {
                    if (
                      !active.has(
                        item.key
                      )
                    ) {
                      continue;
                    }

                    const line =
                      element('div');

                    const label =
                      element(
                        'span',
                        item.label
                      );

                    label.style.color =
                      item.color;

                    line.append(
                      label,
                      element(
                        'b',
                        item.format(
                          row[item.key]
                        )
                      )
                    );

                    tooltip.append(line);
                  }

                  tooltip.style.display =
                    'block';

                  tooltip.style.left =
                    `${Math.min(
                      80,
                      Math.max(
                        6,
                        x / width * 100
                      )
                    )}%`;

                  tooltip.style.top =
                    '1rem';
                }
              );

              hit.addEventListener(
                'mouseleave',
                () => {
                  tooltip.style.display =
                    'none';
                }
              );

              svg.append(hit);
            }
          );

          chartShell.append(
            svg,
            tooltip
          );
        };

      const updateToggle =
        (
          button,
          item
        ) => {
          const enabled =
            active.has(
              item.key
            );

          button.setAttribute(
            'aria-pressed',
            enabled
              ? 'true'
              : 'false'
          );

          button.style.opacity =
            enabled
              ? '1'
              : '0.42';

          button.style.borderColor =
            enabled
              ? item.color
              : 'var(--border)';

          button.style.color =
            enabled
              ? item.color
              : 'var(--muted-foreground)';
        };

      for (const item of series) {
        const button =
          element(
            'button',
            item.label
          );

        button.type =
          'button';

        button.className =
          'goosialize-google-chart-toggle';

        updateToggle(
          button,
          item
        );

        button.addEventListener(
          'click',
          () => {
            if (
              active.has(
                item.key
              )
            ) {
              if (
                active.size === 1
              ) {
                return;
              }

              active.delete(
                item.key
              );
            } else {
              active.add(
                item.key
              );
            }

            updateToggle(
              button,
              item
            );

            renderChart();
          }
        );

        toggles.append(button);
      }

      renderChart();

      root.append(wrapper);
    }

    appendAnalyticsTrendChart(
      root,
      rows
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const data =
        rows
          .map((row) => ({
            date:
              String(
                row?.dimensions?.date
                || ''
              ),
            views:
              Number(
                row?.metrics?.screenPageViews
                || 0
              ),
            sessions:
              Number(
                row?.metrics?.sessions
                || 0
              ),
            events:
              Number(
                row?.metrics?.eventCount
                || 0
              ),
            engagement:
              Number(
                row?.metrics?.engagementRate
                || 0
              ),
            duration:
              Number(
                row?.metrics?.averageSessionDuration
                || 0
              ),
          }))
          .filter(
            (row) =>
              row.date !== ''
          )
          .sort(
            (a, b) =>
              a.date.localeCompare(
                b.date
              )
          );

      if (data.length === 0) {
        return;
      }

      const wrapper =
        element('section');

      wrapper.className =
        'goosialize-google-links-chart';

      const header =
        element('div');

      header.className =
        'goosialize-google-links-chart-header';

      const headingBlock =
        element('div');

      const heading =
        element(
          'h3',
          'Visitors over time'
        );

      const period =
        element(
          'p',
          `${formatGaDate(data[0].date)} — ${formatGaDate(data[data.length - 1].date)}`
        );

      headingBlock.append(
        heading,
        period
      );

      const series = [
        {
          key: 'views',
          label: 'Views',
          color: '#8b5cf6',
          format: formatInteger,
          enabled: true,
        },
        {
          key: 'sessions',
          label: 'Sessions',
          color: '#3b82f6',
          format: formatInteger,
          enabled: true,
        },
        {
          key: 'events',
          label: 'Events',
          color: '#ec4899',
          format: formatInteger,
          enabled: true,
        },
        {
          key: 'engagement',
          label: 'Engagement',
          color: '#22c55e',
          format: formatPercent,
          enabled: false,
        },
        {
          key: 'duration',
          label: 'Avg. session',
          color: '#f59e0b',
          format: formatDuration,
          enabled: false,
        },
      ];

      const active =
        new Set(
          series
            .filter(
              (item) =>
                item.enabled
            )
            .map(
              (item) =>
                item.key
            )
        );

      const toggles =
        element('div');

      toggles.className =
        'goosialize-google-chart-toggles';

      header.append(
        headingBlock,
        toggles
      );

      const chartShell =
        element('div');

      chartShell.className =
        'goosialize-google-links-chart-shell';

      wrapper.append(
        header,
        chartShell
      );

      const renderChart =
        () => {
          chartShell.replaceChildren();

          const ns =
            'http://www.w3.org/2000/svg';

          const svg =
            document.createElementNS(
              ns,
              'svg'
            );

          const width = 900;
          const height = 280;
          const left = 38;
          const right = 16;
          const top = 18;
          const bottom = 34;

          svg.setAttribute(
            'viewBox',
            `0 0 ${width} ${height}`
          );

          svg.setAttribute(
            'preserveAspectRatio',
            'xMidYMid meet'
          );

          svg.style.width =
            '100%';

          svg.style.height =
            '100%';

          svg.style.display =
            'block';

          const plotWidth =
            width - left - right;

          const plotHeight =
            height - top - bottom;

          const xFor =
            (index) =>
              left
              + (
                  data.length === 1
                    ? plotWidth / 2
                    : (
                        plotWidth
                        * index
                        / (
                          data.length - 1
                        )
                      )
                );

          const maxima = {};

          for (
            const item
            of series
          ) {
            maxima[item.key] =
              Math.max(
                1,
                ...data.map(
                  (row) =>
                    Number(
                      row[item.key]
                      || 0
                    )
                )
              );
          }

          const yFor =
            (
              key,
              value
            ) =>
              top
              + plotHeight
              - (
                  Number(value || 0)
                  / maxima[key]
                  * plotHeight
                );

          for (
            let tick = 0;
            tick <= 4;
            tick += 1
          ) {
            const y =
              top
              + (
                  plotHeight
                  * tick
                  / 4
                );

            const line =
              document.createElementNS(
                ns,
                'line'
              );

            line.setAttribute(
              'x1',
              String(left)
            );

            line.setAttribute(
              'x2',
              String(width - right)
            );

            line.setAttribute(
              'y1',
              String(y)
            );

            line.setAttribute(
              'y2',
              String(y)
            );

            line.setAttribute(
              'stroke',
              'currentColor'
            );

            line.setAttribute(
              'stroke-opacity',
              '0.08'
            );

            line.setAttribute(
              'stroke-dasharray',
              '3 3'
            );

            svg.append(line);
          }

          const buildCurve =
            (points) =>
              points
                .map(
                  (
                    point,
                    index
                  ) => {
                    if (index === 0) {
                      return (
                        `M ${point.x},${point.y}`
                      );
                    }

                    const previous =
                      points[index - 1];

                    const control =
                      (
                        previous.x
                        + point.x
                      ) / 2;

                    return (
                      `C ${control},${previous.y} ` +
                      `${control},${point.y} ` +
                      `${point.x},${point.y}`
                    );
                  }
                )
                .join(' ');

          for (
            const item
            of series
          ) {
            if (
              !active.has(
                item.key
              )
            ) {
              continue;
            }

            const points =
              data.map(
                (
                  row,
                  index
                ) => ({
                  x:
                    xFor(index),

                  y:
                    yFor(
                      item.key,
                      row[item.key]
                    ),
                })
              );

            const path =
              document.createElementNS(
                ns,
                'path'
              );

            path.setAttribute(
              'd',
              buildCurve(points)
            );

            path.setAttribute(
              'fill',
              'none'
            );

            path.setAttribute(
              'stroke',
              item.color
            );

            path.setAttribute(
              'stroke-width',
              '2'
            );

            path.setAttribute(
              'vector-effect',
              'non-scaling-stroke'
            );

            svg.append(path);

            points.forEach(
              (point) => {
                const marker =
                  document.createElementNS(
                    ns,
                    'circle'
                  );

                marker.setAttribute(
                  'cx',
                  String(point.x)
                );

                marker.setAttribute(
                  'cy',
                  String(point.y)
                );

                marker.setAttribute(
                  'r',
                  '4'
                );

                marker.setAttribute(
                  'fill',
                  item.color
                );

                marker.setAttribute(
                  'stroke',
                  item.color
                );

                marker.setAttribute(
                  'stroke-width',
                  '1.5'
                );

                marker.setAttribute(
                  'vector-effect',
                  'non-scaling-stroke'
                );

                svg.append(marker);
              }
            );
          }

          const guide =
            document.createElementNS(
              ns,
              'line'
            );

          guide.setAttribute(
            'y1',
            String(top)
          );

          guide.setAttribute(
            'y2',
            String(
              top + plotHeight
            )
          );

          guide.setAttribute(
            'stroke',
            'currentColor'
          );

          guide.setAttribute(
            'stroke-opacity',
            '0'
          );

          guide.setAttribute(
            'stroke-dasharray',
            '3 3'
          );

          svg.append(guide);

          const tooltip =
            element('div');

          tooltip.className =
            'goosialize-google-chart-tooltip';

          data.forEach(
            (
              row,
              index
            ) => {
              const x =
                xFor(index);

              const previous =
                index === 0
                  ? left
                  : (
                      xFor(
                        index - 1
                      )
                      + x
                    ) / 2;

              const next =
                index
                  === data.length - 1
                  ? width - right
                  : (
                      x
                      + xFor(
                        index + 1
                      )
                    ) / 2;

              const hit =
                document.createElementNS(
                  ns,
                  'rect'
                );

              hit.setAttribute(
                'x',
                String(previous)
              );

              hit.setAttribute(
                'y',
                String(top)
              );

              hit.setAttribute(
                'width',
                String(
                  Math.max(
                    1,
                    next - previous
                  )
                )
              );

              hit.setAttribute(
                'height',
                String(plotHeight)
              );

              hit.setAttribute(
                'fill',
                'transparent'
              );

              hit.style.cursor =
                'crosshair';

              hit.addEventListener(
                'mouseenter',
                () => {
                  guide.setAttribute(
                    'x1',
                    String(x)
                  );

                  guide.setAttribute(
                    'x2',
                    String(x)
                  );

                  guide.setAttribute(
                    'stroke-opacity',
                    '0.28'
                  );

                  tooltip.replaceChildren();

                  const title =
                    element(
                      'strong',
                      formatGaDate(
                        row.date
                      )
                    );

                  tooltip.append(
                    title
                  );

                  for (
                    const item
                    of series
                  ) {
                    if (
                      !active.has(
                        item.key
                      )
                    ) {
                      continue;
                    }

                    const line =
                      element('div');

                    const label =
                      element(
                        'span',
                        item.label
                      );

                    label.style.color =
                      item.color;

                    const value =
                      element(
                        'b',
                        item.format(
                          row[item.key]
                        )
                      );

                    line.append(
                      label,
                      value
                    );

                    tooltip.append(
                      line
                    );
                  }

                  tooltip.style.display =
                    'block';

                  tooltip.style.left =
                    `${Math.min(
                      80,
                      Math.max(
                        6,
                        x / width
                        * 100
                      )
                    )}%`;

                  tooltip.style.top =
                    '1rem';
                }
              );

              hit.addEventListener(
                'mouseleave',
                () => {
                  guide.setAttribute(
                    'stroke-opacity',
                    '0'
                  );

                  tooltip.style.display =
                    'none';
                }
              );

              svg.append(hit);
            }
          );

          chartShell.append(
            svg,
            tooltip
          );
        };

      const updateToggle =
        (
          button,
          item
        ) => {
          const enabled =
            active.has(
              item.key
            );

          button.dataset.active =
            enabled
              ? 'true'
              : 'false';

          button.setAttribute(
            'aria-pressed',
            enabled
              ? 'true'
              : 'false'
          );

          button.style.opacity =
            enabled
              ? '1'
              : '0.42';

          button.style.borderColor =
            enabled
              ? item.color
              : 'var(--border)';

          button.style.color =
            enabled
              ? item.color
              : 'var(--muted-foreground)';
        };

      for (
        const item
        of series
      ) {
        const button =
          element(
            'button',
            item.label
          );

        button.type =
          'button';

        button.className =
          'goosialize-google-chart-toggle';

        updateToggle(
          button,
          item
        );

        button.addEventListener(
          'click',
          () => {
            if (
              active.has(
                item.key
              )
            ) {
              if (
                active.size === 1
              ) {
                return;
              }

              active.delete(
                item.key
              );
            } else {
              active.add(
                item.key
              );
            }

            updateToggle(
              button,
              item
            );

            renderChart();
          }
        );

        toggles.append(
          button
        );
      }

      renderChart();

      root.append(wrapper);
    }

    appendBusiestDaysBars(
      root,
      rows
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const order = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
      ];

      const data =
        rows
          .map((row) => ({
            label:
              String(
                row?.dimensions
                  ?.dayOfWeekName
                || ''
              ),
            value:
              Number(
                row?.metrics
                  ?.sessions
                || 0
              ),
          }))
          .filter(
            (item) =>
              item.label !== ''
          )
          .sort(
            (a, b) =>
              order.indexOf(a.label)
              - order.indexOf(b.label)
          );

      if (data.length === 0) {
        return;
      }

      const card =
        element('article');

      card.className =
        'goosialize-google-insight-card';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(
            'Busiest days'
          )
        ),
        document.createTextNode(
          'Busiest days'
        )
      );

      const chart =
        element('div');

      chart.className =
        'goosialize-google-day-bars';

      const max =
        Math.max(
          1,
          ...data.map(
            (item) =>
              item.value
          )
        );

      for (const item of data) {
        const column =
          element('div');

        column.className =
          'goosialize-google-day-column';

        const value =
          element(
            'strong',
            formatInteger(
              item.value
            )
          );

        const track =
          element('div');

        const fill =
          element('span');

        fill.style.height =
          `${
            Math.max(
              4,
              (
                item.value
                / max
              ) * 100
            )
          }%`;

        track.append(fill);

        const label =
          element(
            'small',
            item.label.slice(0, 3)
          );

        column.append(
          value,
          track,
          label
        );

        chart.append(column);
      }

      card.append(
        heading,
        chart
      );

      root.append(card);
    }

    appendBusiestHoursDonut(
      root,
      rows
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const data =
        rows
          .map((row) => ({
            hour:
              String(
                row?.dimensions?.hour
                || ''
              ),
            value:
              Number(
                row?.metrics?.sessions
                || 0
              ),
          }))
          .filter(
            (item) =>
              item.hour !== ''
              && item.value > 0
          )
          .sort(
            (a, b) =>
              b.value - a.value
          )
          .slice(0, 8);

      if (data.length === 0) {
        return;
      }

      const total =
        data.reduce(
          (sum, item) =>
            sum + item.value,
          0
        );

      const colors = [
        '#8b5cf6',
        '#3b82f6',
        '#14b8a6',
        '#ec4899',
        '#f59e0b',
        '#22c55e',
        '#f97316',
        '#6366f1',
      ];

      const card =
        element('article');

      card.className =
        'goosialize-google-insight-card';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(
            'Busiest hours'
          )
        ),
        document.createTextNode(
          'Busiest hours'
        )
      );

      const body =
        element('div');

      body.className =
        'goosialize-google-hour-donut-layout';

      const ns =
        'http://www.w3.org/2000/svg';

      const svg =
        document.createElementNS(
          ns,
          'svg'
        );

      svg.setAttribute(
        'viewBox',
        '0 0 140 140'
      );

      svg.classList.add(
        'goosialize-google-hour-donut'
      );

      const radius = 45;
      const circumference =
        2 * Math.PI * radius;

      let offset = 0;

      data.forEach(
        (item, index) => {
          const ratio =
            item.value / total;

          const circle =
            document.createElementNS(
              ns,
              'circle'
            );

          circle.setAttribute(
            'cx',
            '70'
          );

          circle.setAttribute(
            'cy',
            '70'
          );

          circle.setAttribute(
            'r',
            String(radius)
          );

          circle.setAttribute(
            'fill',
            'none'
          );

          circle.setAttribute(
            'stroke',
            colors[
              index % colors.length
            ]
          );

          circle.setAttribute(
            'stroke-width',
            '16'
          );

          circle.setAttribute(
            'stroke-dasharray',
            `${ratio * circumference} ${circumference}`
          );

          circle.setAttribute(
            'stroke-dashoffset',
            String(
              -offset
              * circumference
            )
          );

          circle.setAttribute(
            'transform',
            'rotate(-90 70 70)'
          );

          const title =
            document.createElementNS(
              ns,
              'title'
            );

          title.textContent =
            `${String(item.hour).padStart(2, '0')}:00 — ${formatInteger(item.value)} sessions`;

          circle.append(title);
          svg.append(circle);

          offset += ratio;
        }
      );

      const center =
        document.createElementNS(
          ns,
          'text'
        );

      center.setAttribute(
        'x',
        '70'
      );

      center.setAttribute(
        'y',
        '74'
      );

      center.setAttribute(
        'text-anchor',
        'middle'
      );

      center.setAttribute(
        'fill',
        'currentColor'
      );

      center.setAttribute(
        'font-size',
        '18'
      );

      center.setAttribute(
        'font-weight',
        '700'
      );

      center.textContent =
        formatInteger(total);

      svg.append(center);

      const legend =
        element('div');

      legend.className =
        'goosialize-google-hour-legend';

      data.forEach(
        (item, index) => {
          const row =
            element('div');

          const dot =
            element('i');

          dot.style.background =
            colors[
              index % colors.length
            ];

          const label =
            element(
              'span',
              `${String(item.hour).padStart(2, '0')}:00`
            );

          const value =
            element(
              'strong',
              formatInteger(
                item.value
              )
            );

          row.append(
            dot,
            label,
            value
          );

          legend.append(row);
        }
      );

      body.append(
        svg,
        legend
      );

      card.append(
        heading,
        body
      );

      root.append(card);
    }

    appendGeoMap(
      root,
      countryRows,
      cityRows
    ) {
      const countries =
        Array.isArray(countryRows)
          ? countryRows
          : [];

      const cities =
        Array.isArray(cityRows)
          ? cityRows
          : [];

      if (
        countries.length === 0
        && cities.length === 0
      ) {
        return;
      }

      const countryPoints = {
        Cyprus: [59.2, 30.6],
        Greece: [56.1, 29.1],
        Germany: [52.8, 21.8],
        France: [50.6, 24.0],
        Italy: [53.5, 27.4],
        Spain: [47.9, 26.1],
        'United Kingdom': [49.2, 19.4],
        Netherlands: [51.4, 20.5],
        Poland: [55.6, 20.9],
        Romania: [57.2, 24.1],
        Bulgaria: [57.2, 26.3],
        Türkiye: [60.0, 27.2],
        Israel: [59.8, 32.9],
        India: [71.8, 43.6],
        China: [79.0, 35.0],
        Japan: [87.8, 38.4],
        Australia: [84.8, 75.8],
        Canada: [20.7, 18.9],
        'United States': [22.6, 35.4],
        Mexico: [21.8, 47.7],
        Brazil: [35.6, 66.3],
        Argentina: [32.0, 82.0],
      };

      const card =
        element('section');

      card.className =
        'goosialize-google-geo-card';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(
            'Visitor locations'
          )
        ),
        document.createTextNode(
          'Visitor locations'
        )
      );

      const body =
        element('div');

      body.className =
        'goosialize-google-geo-body';

      const map =
        element('div');

      map.className =
        'goosialize-google-map-stage';

      const image =
        element('img');

      image.src =
        '/user/plugins/goosialize-google/admin-next/assets/world.svg';

      image.alt =
        'World map';

      image.className =
        'goosialize-google-geo-map';

      map.append(image);

      for (
        const row
        of countries
      ) {
        const country =
          String(
            row?.dimensions?.country
            || ''
          );

        const point =
          countryPoints[country];

        if (!point) {
          continue;
        }

        const marker =
          element('span');

        marker.className =
          'goosialize-google-map-dot';

        marker.style.left =
          `${point[0]}%`;

        marker.style.top =
          `${point[1]}%`;

        marker.title =
          `${country}: ${formatInteger(
            row?.metrics?.activeUsers
            || 0
          )} users`;

        map.append(marker);
      }

      const cityList =
        element('div');

      cityList.className =
        'goosialize-google-city-list';

      const validCities =
        cities.filter(
          (row) => {
            const city =
              String(
                row?.dimensions?.city
                || ''
              ).trim();

            const normalized =
              city.toLowerCase();

            return (
              city !== ''
              && normalized !== '(not set)'
              && normalized !== 'not set'
            );
          }
        );

      if (
        validCities.length > 0
      ) {
        cityList.append(
          element(
            'strong',
            'Top cities'
          )
        );

        validCities
          .slice(0, 8)
          .forEach(
            (row) => {
              const city =
                String(
                  row?.dimensions?.city
                  || ''
                );

              const country =
                String(
                  row?.dimensions?.country
                  || ''
                );

              const item =
                element('div');

              const label =
                element(
                  'span',
                  country
                    ? `${city}, ${country}`
                    : city
                );

              const value =
                element(
                  'strong',
                  formatInteger(
                    row?.metrics?.activeUsers
                    || 0
                  )
                );

              item.append(
                label,
                value
              );

              cityList.append(item);
            }
          );
      } else {
        cityList.append(
          element(
            'strong',
            'City data unavailable'
          )
        );

        const fallbackCountries =
          countries
            .filter(
              (row) =>
                String(
                  row?.dimensions?.country
                  || ''
                ).trim() !== ''
            )
            .slice(0, 5);

        fallbackCountries.forEach(
          (row) => {
            const country =
              String(
                row?.dimensions?.country
                || ''
              );

            const item =
              element('div');

            item.append(
              element(
                'span',
                country
              ),
              element(
                'strong',
                formatInteger(
                  row?.metrics?.activeUsers
                  || 0
                )
              )
            );

            cityList.append(item);
          }
        );

        const note =
          element(
            'small',
            'Google Analytics did not report city-level data for this period.'
          );

        note.className =
          'goosialize-google-city-note';

        cityList.append(note);
      }

      body.append(
        map,
        cityList
      );

      card.append(
        heading,
        body
      );

      root.append(card);
    }

    appendAdminInsight(
      root,
      title,
      rows,
      dimensionKey,
      metricKey,
      metricLabel,
      labelFormatter = null
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const values =
        rows
          .map((row) => ({
            label:
              String(
                row?.dimensions?.[
                  dimensionKey
                ]
                || '(not set)'
              ),
            value:
              Number(
                row?.metrics?.[
                  metricKey
                ]
                || 0
              ),
          }))
          .filter(
            (item) =>
              item.value > 0
              && item.label !== '(not set)'
          )
          .sort(
            (a, b) =>
              b.value - a.value
          )
          .slice(0, 8);

      if (values.length === 0) {
        return;
      }

      const max =
        Math.max(
          1,
          ...values.map(
            (item) =>
              item.value
          )
        );

      const total =
        values.reduce(
          (sum, item) =>
            sum + item.value,
          0
        );

      const card =
        element('article');

      card.className =
        'goosialize-google-insight-card';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(title),
          `${title} information`
        ),
        document.createTextNode(
          title
        )
      );

      const list =
        element('div');

      list.className =
        'goosialize-google-insight-list';

      for (
        const item
        of values
      ) {
        const row =
          element('div');

        row.className =
          'goosialize-google-insight-row';

        const top =
          element('div');

        const label =
          element(
            'span',
            labelFormatter
              ? labelFormatter(
                  item.label
                )
              : item.label
          );

        const number =
          element(
            'strong',
            formatInteger(
              item.value
            )
          );

        top.append(
          label,
          number
        );

        const track =
          element('div');

        track.className =
          'goosialize-google-insight-track';

        const fill =
          element('span');

        fill.style.width =
          `${
            item.value
            / max
            * 100
          }%`;

        track.append(fill);

        const meta =
          element(
            'small',
            `${(
              item.value
              / total
              * 100
            ).toFixed(1)}% · ${metricLabel}`
          );

        row.append(
          top,
          track,
          meta
        );

        list.append(row);
      }

      card.append(
        heading,
        list
      );

      root.append(card);
    }

    appendTable(
      root,
      title,
      rows,
      dimensions,
      metrics
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const section =
        element('article');

      section.className =
        'overflow-hidden border border-border bg-card';

      section.style.borderRadius =
        '0.5rem';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(title),
          `${title} information`
        ),
        document.createTextNode(
          title
        )
      );

      heading.className =
        'border-b border-border px-4 py-2.5 font-semibold';

      heading.style.fontSize =
        '0.875rem';

      section.append(heading);

      const scroll =
        element('div');

      scroll.className =
        'overflow-x-auto';

      const table =
        element('table');

      table.className =
        'w-full text-sm';

      const head =
        element('thead');

      head.className =
        'bg-muted/50 text-left text-muted-foreground';

      const headRow =
        element('tr');

      for (
        const [, label]
        of [...dimensions, ...metrics]
      ) {
        const th =
          element(
            'th',
            label
          );

        th.className =
          'px-3 py-2 text-left text-xs font-medium';

        headRow.append(th);
      }

      head.append(headRow);
      table.append(head);

      const body =
        element('tbody');

      for (const row of rows) {
        const tr =
          element('tr');

        tr.className =
          'border-t border-border';

        for (
          const [key]
          of dimensions
        ) {
          const td =
            element(
              'td',
              row?.dimensions?.[key]
              ?? ''
            );

          td.className =
            'px-3 py-2';

          tr.append(td);
        }

        for (
          const [key]
          of metrics
        ) {
          const td =
            element(
              'td',
              formatInteger(
                row?.metrics?.[key]
              )
            );

          td.className =
            'px-3 py-2 text-right tabular-nums';

          tr.append(td);
        }

        body.append(tr);
      }

      table.append(body);
      scroll.append(table);
      section.append(scroll);
      root.append(section);
    }

    appendSearchConsoleInsight(
      root,
      title,
      rows,
      dimensionKey
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const data =
        rows
          .map(
            (row) => ({
              label:
                String(
                  row?.[dimensionKey]
                  || ''
                ),
              clicks:
                Number(
                  row?.clicks
                  || 0
                ),
              impressions:
                Number(
                  row?.impressions
                  || 0
                ),
            })
          )
          .filter(
            (item) =>
              item.label !== ''
              && item.label
                !== '(not set)'
          )
          .sort(
            (a, b) =>
              b.impressions
              - a.impressions
          )
          .slice(0, 8);

      if (data.length === 0) {
        return;
      }

      const card =
        element('article');

      card.className =
        'goosialize-google-insight-card';

      const heading =
        element('h3');

      heading.append(
        googleInfoIcon(
          googleMetricHelp(title),
          `${title} information`
        ),
        document.createTextNode(
          title
        )
      );

      const max =
        Math.max(
          1,
          ...data.map(
            (item) =>
              item.impressions
          )
        );

      for (const item of data) {
        const row =
          element('div');

        row.className =
          'goosialize-google-insight-row';

        const top =
          element('div');

        top.className =
          'goosialize-google-insight-row-top';

        top.append(
          element(
            'span',
            item.label
          ),
          element(
            'strong',
            formatInteger(
              item.impressions
            )
          )
        );

        const track =
          element('div');

        track.className =
          'goosialize-google-insight-track';

        const fill =
          element('span');

        fill.style.width =
          `${
            item.impressions
            / max
            * 100
          }%`;

        track.append(fill);

        row.append(
          top,
          track,
          element(
            'small',
            `${formatInteger(
              item.clicks
            )} clicks · ${formatInteger(
              item.impressions
            )} impressions`
          )
        );

        card.append(row);
      }

      root.append(card);
    }

    appendSearchConsoleTable(
      root,
      title,
      rows,
      dimensions,
      metrics
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const section =
        element('article');

      section.className =
        'overflow-hidden rounded-lg border border-border bg-card';

      const heading =
        element(
          'h3',
          title
        );

      heading.className =
        'border-b border-border px-4 py-2.5 font-semibold';

      section.append(heading);

      const scroll =
        element('div');

      scroll.className =
        'overflow-x-auto';

      const table =
        element('table');

      table.className =
        'w-full text-sm';

      const head =
        element('thead');

      head.className =
        'bg-muted/50 text-left text-muted-foreground';

      const headRow =
        element('tr');

      for (
        const [, label]
        of [...dimensions, ...metrics]
      ) {
        const th =
          element(
            'th',
            label
          );

        th.className =
          'px-3 py-2 text-left text-xs font-medium';

        headRow.append(th);
      }

      head.append(headRow);
      table.append(head);

      const body =
        element('tbody');

      for (const row of rows) {
        const tr =
          element('tr');

        tr.className =
          'border-t border-border';

        for (
          const [key]
          of dimensions
        ) {
          const td =
            element(
              'td',
              row?.[key]
              ?? ''
            );

          td.className =
            'px-3 py-2';

          tr.append(td);
        }

        for (
          const [
            key,
            ,
            formatter,
          ]
          of metrics
        ) {
          const td =
            element(
              'td',
              formatter(
                row?.[key]
                ?? 0
              )
            );

          td.className =
            'px-3 py-2 text-right tabular-nums';

          tr.append(td);
        }

        body.append(tr);
      }

      table.append(body);
      scroll.append(table);
      section.append(scroll);
      root.append(section);
    }

  }

  customElements.define(
    TAG,
    GoosializeGooglePage
  );
})();
