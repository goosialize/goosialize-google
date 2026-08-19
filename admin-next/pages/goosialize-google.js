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

        const dashboardPath =
          this.product === 'search_console'
            ? `/goosialize-google/search-console/performance?${query.toString()}`
            : `/goosialize-google/analytics?${query.toString()}`;

        this.data =
          await this.apiGet(
            dashboardPath
          );
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
        'mt-1 inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm';

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

      const refreshGroup =
        element('div');

      refreshGroup.className =
        'shrink-0';

      const refreshSpacer =
        element(
          'div',
          'Refresh'
        );

      refreshSpacer.className =
        'invisible text-xs font-medium text-muted-foreground';

      refreshSpacer.setAttribute(
        'aria-hidden',
        'true'
      );

      const refreshButton =
        element(
          'button',
          this.loading
            ? 'Refreshing…'
            : 'Refresh'
        );

      refreshButton.type =
        'button';

      refreshButton.disabled =
        this.loading
        || this.propertyId === '';

      refreshButton.className =
        'mt-1 inline-flex h-10 items-center justify-center rounded-md border border-border px-3 text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:cursor-not-allowed disabled:opacity-50';

      refreshButton.setAttribute(
        'aria-label',
        this.product === 'search_console'
          ? 'Refresh Search Console data'
          : 'Refresh Google Analytics data'
      );

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

      refreshGroup.append(
        refreshSpacer,
        refreshButton
      );

      toolbar.append(
        propertyGroup,
        periodGroup,
        refreshGroup
      );

      root.append(toolbar);

      if (this.loading) {
        const progress =
          element('div');

        progress.className =
          'h-0.5 w-full overflow-hidden rounded-full bg-muted';

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
          'h-full w-1/3 rounded-full bg-primary';

        progressBar.animate(
          [
            {
              transform:
                'translateX(-120%)',
            },
            {
              transform:
                'translateX(320%)',
            },
          ],
          {
            duration: 900,
            iterations: Infinity,
            easing: 'ease-in-out',
          }
        );

        progress.append(
          progressBar
        );

        root.append(
          progress
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
              {
                label: 'Clicks',
                value: formatInteger(
                  metrics.clicks
                ),
                icon: '↗',
                accent: '#a855f7',
              },
              {
                label: 'Impressions',
                value: formatInteger(
                  metrics.impressions
                ),
                icon: '◉',
                accent: '#3b82f6',
              },
              {
                label: 'CTR',
                value: formatPercent(
                  metrics.ctr
                ),
                icon: '%',
                accent: '#22c55e',
              },
              {
                label: 'Average position',
                value: formatDecimal(
                  metrics.position,
                  1
                ),
                icon: '#',
                accent: '#f59e0b',
              },
            ]
          : [
              {
                label: 'Active users',
                value: formatInteger(
                  metrics.activeUsers
                ),
                icon: '●',
                accent: '#a855f7',
              },
              {
                label: 'New users',
                value: formatInteger(
                  metrics.newUsers
                ),
                icon: '+',
                accent: '#8b5cf6',
              },
              {
                label: 'Sessions',
                value: formatInteger(
                  metrics.sessions
                ),
                icon: '↻',
                accent: '#3b82f6',
              },
              {
                label: 'Views',
                value: formatInteger(
                  metrics.screenPageViews
                ),
                icon: '◉',
                accent: '#06b6d4',
              },
              {
                label: 'Engagement',
                value: formatPercent(
                  metrics.engagementRate
                ),
                icon: '◆',
                accent: '#22c55e',
              },
              {
                label: 'Avg. session',
                value: formatDuration(
                  metrics.averageSessionDuration
                ),
                icon: '◷',
                accent: '#f59e0b',
              },
              {
                label: 'Events',
                value: formatInteger(
                  metrics.eventCount
                ),
                icon: 'ϟ',
                accent: '#ec4899',
              },
              {
                label: 'Key events',
                value: formatInteger(
                  metrics.keyEvents
                ),
                icon: '★',
                accent: '#f97316',
              },
            ];

      for (
        const definition
        of definitions
      ) {
        const card =
          element('article');

        card.className =
          'relative min-h-24 overflow-hidden rounded-lg border border-border bg-card p-4';

        card.style.borderTop =
          `2px solid ${definition.accent}`;

        const top =
          element('div');

        top.className =
          'flex min-h-8 items-center justify-between gap-3';

        const labelNode =
          element(
            'div',
            definition.label
          );

        labelNode.className =
          'block text-sm font-medium';

        labelNode.textContent =
          definition.label;

        labelNode.style.display =
          'block';

        labelNode.style.minHeight =
          '20px';

        labelNode.style.opacity =
          '0.78';

        labelNode.style.visibility =
          'visible';

        labelNode.style.color =
          'currentColor';

        const iconNode =
          element(
            'span',
            definition.icon
          );

        iconNode.className =
          'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-sm font-semibold';

        iconNode.style.color =
          definition.accent;

        iconNode.style.background =
          `${definition.accent}18`;

        top.append(
          labelNode,
          iconNode
        );

        const valueNode =
          element(
            'strong',
            definition.value
          );

        valueNode.className =
          'mt-2 block text-2xl font-semibold tabular-nums text-foreground';

        card.append(
          top,
          valueNode
        );

        cards.append(card);
      }

      root.append(cards);

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

        this.appendSearchConsoleTable(
          tables,
          'Top queries',
          this.data.top_queries,
          [['query', 'Query']],
          searchMetrics
        );

        this.appendSearchConsoleTable(
          tables,
          'Top pages',
          this.data.top_pages,
          [['page', 'Page']],
          searchMetrics
        );

        this.appendSearchConsoleTable(
          tables,
          'Devices',
          this.data.devices,
          [['device', 'Device']],
          searchMetrics
        );

        this.appendSearchConsoleTable(
          tables,
          'Countries',
          this.data.countries,
          [['country', 'Country']],
          searchMetrics
        );
      } else {
        const visualGrid =
          element('section');

        visualGrid.className =
          'goosialize-google-detail-grid';

        this.appendDonutBreakdown(
          visualGrid,
          'Traffic channels',
          this.data.traffic_channels,
          'sessionDefaultChannelGroup',
          'sessions',
          'Sessions'
        );

        this.appendDonutBreakdown(
          visualGrid,
          'Devices',
          this.data.devices,
          'deviceCategory',
          'sessions',
          'Sessions'
        );

        if (
          visualGrid.childElementCount > 0
        ) {
          root.append(visualGrid);
        }

        this.appendCountryMap(
          root,
          this.data.countries
        );

        this.appendTable(
          tables,
          'Top pages',
          this.data.top_pages,
          [['pagePath', 'Page']],
          [
            ['screenPageViews', 'Views'],
            ['activeUsers', 'Users'],
          ]
        );

        this.appendTable(
          tables,
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

      const normalized =
        rows
          .map((row) => ({
            date:
              String(
                row?.dimensions?.date
                || ''
              ),
            users:
              Number(
                row?.metrics?.activeUsers
                || 0
              ),
            sessions:
              Number(
                row?.metrics?.sessions
                || 0
              ),
            views:
              Number(
                row?.metrics?.screenPageViews
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

      if (normalized.length === 0) {
        return;
      }

      const section =
        element('section');

      section.className =
        'overflow-hidden rounded-lg border border-border bg-card';

      const header =
        element('div');

      header.className =
        'flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between';

      const heading =
        element(
          'h3',
          'Analytics trend'
        );

      heading.className =
        'font-semibold text-foreground';

      const legend =
        element('div');

      legend.className =
        'flex flex-wrap items-center gap-4 text-xs text-muted-foreground';

      const series = [
        {
          key: 'users',
          label: 'Users',
          color: '#a855f7',
          dash: '',
          radius: 5,
        },
        {
          key: 'sessions',
          label: 'Sessions',
          color: '#3b82f6',
          dash: '9 5',
          radius: 4,
        },
        {
          key: 'views',
          label: 'Views',
          color: '#06b6d4',
          dash: '2 5',
          radius: 3,
        },
      ];

      for (
        const item
        of series
      ) {
        const legendItem =
          element('span');

        legendItem.className =
          'inline-flex items-center gap-1.5';

        const sample =
          element('span');

        sample.className =
          'inline-block h-0.5 w-5 rounded';

        sample.style.background =
          item.color;

        legendItem.append(
          sample,
          document.createTextNode(
            item.label
          )
        );

        legend.append(
          legendItem
        );
      }

      header.append(
        heading,
        legend
      );

      const chartWrap =
        element('div');

      chartWrap.className =
        'relative px-3 pt-3 text-muted-foreground';

      const chart =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'svg'
        );

      const width = 900;
      const height = 280;
      const left = 54;
      const right = 20;
      const top = 18;
      const bottom = 44;

      const plotWidth =
        width - left - right;

      const plotHeight =
        height - top - bottom;

      chart.setAttribute(
        'viewBox',
        `0 0 ${width} ${height}`
      );

      chart.setAttribute(
        'role',
        'img'
      );

      chart.setAttribute(
        'aria-label',
        'Users, sessions and views by date'
      );

      chart.style.width =
        '100%';

      chart.style.display =
        'block';

      const maxValue =
        Math.max(
          1,
          ...normalized.flatMap(
            (row) => [
              row.users,
              row.sessions,
              row.views,
            ]
          )
        );

      const ns =
        'http://www.w3.org/2000/svg';

      const createSvg =
        (
          tag,
          attributes = {}
        ) => {
          const node =
            document.createElementNS(
              ns,
              tag
            );

          for (
            const [key, value]
            of Object.entries(
              attributes
            )
          ) {
            node.setAttribute(
              key,
              String(value)
            );
          }

          return node;
        };

      for (
        let index = 0;
        index <= 4;
        index += 1
      ) {
        const y =
          top
          + (
            plotHeight
            * index
            / 4
          );

        const value =
          Math.round(
            maxValue
            * (
              1
              - index / 4
            )
          );

        chart.append(
          createSvg(
            'line',
            {
              x1: left,
              x2: width - right,
              y1: y,
              y2: y,
              stroke: 'currentColor',
              opacity: '0.10',
            }
          )
        );

        const label =
          createSvg(
            'text',
            {
              x: left - 10,
              y: y + 4,
              'text-anchor': 'end',
              fill: 'currentColor',
              opacity: '0.65',
              'font-size': 11,
            }
          );

        label.textContent =
          String(value);

        chart.append(label);
      }

      const xFor =
        (index) =>
          left
          + (
            normalized.length === 1
              ? plotWidth / 2
              : (
                  plotWidth
                  * index
                  / (
                    normalized.length
                    - 1
                  )
                )
          );

      const yFor =
        (value) =>
          top
          + plotHeight
          - (
              Number(value || 0)
              / maxValue
              * plotHeight
            );

      for (
        const item
        of series
      ) {
        const points =
          normalized.map(
            (row, index) =>
              `${xFor(index)},${yFor(row[item.key])}`
          );

        const polyline =
          createSvg(
            'polyline',
            {
              points:
                points.join(' '),
              fill: 'none',
              stroke: item.color,
              'stroke-width': 2.5,
              'stroke-linecap': 'round',
              'stroke-linejoin': 'round',
            }
          );

        if (
          item.dash !== ''
        ) {
          polyline.setAttribute(
            'stroke-dasharray',
            item.dash
          );
        }

        chart.append(polyline);

        normalized.forEach(
          (row, index) => {
            const outer =
              createSvg(
                'circle',
                {
                  cx: xFor(index),
                  cy: yFor(
                    row[item.key]
                  ),
                  r: item.radius + 2,
                  fill: 'var(--card, #18181b)',
                  stroke: item.color,
                  'stroke-width': 1.5,
                }
              );

            const point =
              createSvg(
                'circle',
                {
                  cx: xFor(index),
                  cy: yFor(
                    row[item.key]
                  ),
                  r: item.radius,
                  fill: item.color,
                }
              );

            chart.append(
              outer,
              point
            );
          }
        );
      }

      const guide =
        createSvg(
          'line',
          {
            x1: left,
            x2: left,
            y1: top,
            y2: top + plotHeight,
            stroke: 'currentColor',
            opacity: '0',
            'stroke-width': 1,
            'stroke-dasharray': '4 4',
          }
        );

      chart.append(guide);

      const tooltip =
        element('div');

      tooltip.className =
        'pointer-events-none absolute z-20 hidden min-w-44 rounded-md border border-border bg-popover px-3 py-2 text-xs text-popover-foreground shadow-lg';

      chartWrap.append(
        chart,
        tooltip
      );

      normalized.forEach(
        (row, index) => {
          const previous =
            index === 0
              ? left
              : (
                  xFor(index - 1)
                  + xFor(index)
                ) / 2;

          const next =
            index
              === normalized.length - 1
              ? width - right
              : (
                  xFor(index)
                  + xFor(index + 1)
                ) / 2;

          const hit =
            createSvg(
              'rect',
              {
                x: previous,
                y: top,
                width:
                  Math.max(
                    1,
                    next - previous
                  ),
                height: plotHeight,
                fill: 'transparent',
                style:
                  'cursor:crosshair',
              }
            );

          hit.addEventListener(
            'mouseenter',
            () => {
              const x =
                xFor(index);

              guide.setAttribute(
                'x1',
                String(x)
              );

              guide.setAttribute(
                'x2',
                String(x)
              );

              guide.setAttribute(
                'opacity',
                '0.35'
              );

              tooltip.replaceChildren();

              const date =
                element(
                  'strong',
                  formatGaDate(
                    row.date
                  )
                );

              date.className =
                'mb-2 block text-foreground';

              tooltip.append(date);

              for (
                const item
                of series
              ) {
                const line =
                  element('div');

                line.className =
                  'flex items-center justify-between gap-5 py-0.5';

                const label =
                  element('span');

                label.className =
                  'inline-flex items-center gap-1.5 text-muted-foreground';

                const dot =
                  element('span');

                dot.className =
                  'h-2 w-2 rounded-full';

                dot.style.background =
                  item.color;

                label.append(
                  dot,
                  document.createTextNode(
                    item.label
                  )
                );

                const value =
                  element(
                    'strong',
                    formatInteger(
                      row[item.key]
                    )
                  );

                value.className =
                  'tabular-nums text-foreground';

                line.append(
                  label,
                  value
                );

                tooltip.append(line);
              }

              tooltip.classList.remove(
                'hidden'
              );

              const percent =
                x / width;

              tooltip.style.left =
                `${Math.min(
                  82,
                  Math.max(
                    8,
                    percent * 100
                  )
                )}%`;

              tooltip.style.top =
                '20px';

              tooltip.style.transform =
                percent > 0.65
                  ? 'translateX(-100%)'
                  : 'translateX(0)';
            }
          );

          hit.addEventListener(
            'mouseleave',
            () => {
              guide.setAttribute(
                'opacity',
                '0'
              );

              tooltip.classList.add(
                'hidden'
              );
            }
          );

          chart.append(hit);
        }
      );

      const xLabels =
        createSvg(
          'g'
        );

      const labelIndexes =
        Array.from(
          new Set([
            0,
            Math.floor(
              (normalized.length - 1) / 2
            ),
            normalized.length - 1,
          ])
        );

      for (
        const index
        of labelIndexes
      ) {
        const label =
          createSvg(
            'text',
            {
              x: xFor(index),
              y: height - 12,
              'text-anchor':
                index === 0
                  ? 'start'
                  : (
                      index
                      === normalized.length - 1
                        ? 'end'
                        : 'middle'
                    ),
              fill: 'currentColor',
              opacity: '0.65',
              'font-size': 11,
            }
          );

        label.textContent =
          formatGaDate(
            normalized[index].date
          );

        xLabels.append(label);
      }

      chart.append(xLabels);

      section.append(
        header,
        chartWrap
      );

      root.append(section);
    }

    appendDonutBreakdown(
      root,
      title,
      rows,
      dimensionKey,
      metricKey,
      metricLabel
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const items =
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
          );

      if (items.length === 0) {
        return;
      }

      const total =
        items.reduce(
          (sum, item) =>
            sum + item.value,
          0
        );

      const colors = [
        '#a855f7',
        '#3b82f6',
        '#06b6d4',
        '#22c55e',
        '#f59e0b',
        '#ec4899',
        '#f97316',
      ];

      const card =
        element('article');

      card.className =
        'rounded-lg border border-border bg-card';

      const heading =
        element(
          'h3',
          title
        );

      heading.className =
        'border-b border-border px-4 py-2.5 font-semibold';

      const body =
        element('div');

      body.className =
        'grid gap-4 p-4 sm:grid-cols-[140px_minmax(0,1fr)] sm:items-center';

      const chart =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'svg'
        );

      chart.setAttribute(
        'viewBox',
        '0 0 180 180'
      );

      chart.classList.add(
        'mx-auto',
        'h-32',
        'w-32'
      );

      const centerX = 90;
      const centerY = 90;
      const radius = 62;
      const circumference =
        2 * Math.PI * radius;

      const background =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'circle'
        );

      background.setAttribute(
        'cx',
        String(centerX)
      );
      background.setAttribute(
        'cy',
        String(centerY)
      );
      background.setAttribute(
        'r',
        String(radius)
      );
      background.setAttribute(
        'fill',
        'none'
      );
      background.setAttribute(
        'stroke',
        'currentColor'
      );
      background.setAttribute(
        'opacity',
        '0.10'
      );
      background.setAttribute(
        'stroke-width',
        '22'
      );

      chart.append(background);

      let offset = 0;

      items.forEach(
        (item, index) => {
          const ratio =
            item.value / total;

          const segment =
            document.createElementNS(
              'http://www.w3.org/2000/svg',
              'circle'
            );

          segment.setAttribute(
            'cx',
            String(centerX)
          );
          segment.setAttribute(
            'cy',
            String(centerY)
          );
          segment.setAttribute(
            'r',
            String(radius)
          );
          segment.setAttribute(
            'fill',
            'none'
          );
          segment.setAttribute(
            'stroke',
            colors[
              index % colors.length
            ]
          );
          segment.setAttribute(
            'stroke-width',
            '22'
          );
          segment.setAttribute(
            'stroke-linecap',
            'butt'
          );
          segment.setAttribute(
            'stroke-dasharray',
            `${ratio * circumference} ${circumference}`
          );
          segment.setAttribute(
            'stroke-dashoffset',
            String(
              -offset * circumference
            )
          );
          segment.setAttribute(
            'transform',
            'rotate(-90 90 90)'
          );

          chart.append(segment);

          offset += ratio;
        }
      );

      const totalLabel =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'text'
        );

      totalLabel.setAttribute(
        'x',
        '90'
      );
      totalLabel.setAttribute(
        'y',
        '86'
      );
      totalLabel.setAttribute(
        'text-anchor',
        'middle'
      );
      totalLabel.setAttribute(
        'fill',
        'currentColor'
      );
      totalLabel.setAttribute(
        'font-size',
        '24'
      );
      totalLabel.setAttribute(
        'font-weight',
        '700'
      );

      totalLabel.textContent =
        formatInteger(total);

      const totalCaption =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'text'
        );

      totalCaption.setAttribute(
        'x',
        '90'
      );
      totalCaption.setAttribute(
        'y',
        '106'
      );
      totalCaption.setAttribute(
        'text-anchor',
        'middle'
      );
      totalCaption.setAttribute(
        'fill',
        'currentColor'
      );
      totalCaption.setAttribute(
        'opacity',
        '0.65'
      );
      totalCaption.setAttribute(
        'font-size',
        '11'
      );

      totalCaption.textContent =
        metricLabel;

      chart.append(
        totalLabel,
        totalCaption
      );

      const legend =
        element('div');

      legend.className =
        'space-y-2';

      items.forEach(
        (item, index) => {
          const row =
            element('div');

          row.className =
            'grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-3 text-sm';

          const label =
            element('div');

          label.className =
            'flex min-w-0 items-center gap-2';

          const dot =
            element('span');

          dot.className =
            'h-2.5 w-2.5 shrink-0 rounded-full';

          dot.style.background =
            colors[
              index % colors.length
            ];

          const name =
            element(
              'span',
              item.label
            );

          name.className =
            'truncate text-foreground';

          label.append(
            dot,
            name
          );

          const value =
            element(
              'strong',
              formatInteger(
                item.value
              )
            );

          value.className =
            'tabular-nums';

          const percent =
            element(
              'span',
              `${(
                item.value
                / total
                * 100
              ).toFixed(1)}%`
            );

          percent.className =
            'w-14 text-right tabular-nums text-muted-foreground';

          row.append(
            label,
            value,
            percent
          );

          legend.append(row);
        }
      );

      body.append(
        chart,
        legend
      );

      card.append(
        heading,
        body
      );

      root.append(card);
    }

    appendCountryMap(
      root,
      rows
    ) {
      if (
        !Array.isArray(rows)
        || rows.length === 0
      ) {
        return;
      }

      const items =
        rows
          .map((row) => ({
            country:
              String(
                row?.dimensions?.country
                || ''
              ),
            users:
              Number(
                row?.metrics?.activeUsers
                || 0
              ),
            sessions:
              Number(
                row?.metrics?.sessions
                || 0
              ),
          }))
          .filter(
            (item) =>
              item.country !== ''
              && item.country !== '(not set)'
          );

      if (items.length === 0) {
        return;
      }

      const coordinates = {
        'United States': [-100, 38],
        'Canada': [-106, 56],
        'Mexico': [-102, 23],
        'Brazil': [-52, -10],
        'Argentina': [-64, -34],
        'United Kingdom': [-3, 55],
        'Ireland': [-8, 53],
        'France': [2, 46],
        'Germany': [10, 51],
        'Spain': [-4, 40],
        'Portugal': [-8, 39],
        'Italy': [12, 42],
        'Greece': [22, 39],
        'Cyprus': [33, 35],
        'Netherlands': [5, 52],
        'Belgium': [4, 51],
        'Switzerland': [8, 47],
        'Austria': [14, 47],
        'Poland': [20, 52],
        'Sweden': [15, 62],
        'Norway': [8, 61],
        'Finland': [26, 64],
        'Denmark': [10, 56],
        'Romania': [25, 46],
        'Bulgaria': [25, 43],
        'Türkiye': [35, 39],
        'Turkey': [35, 39],
        'Israel': [35, 31],
        'United Arab Emirates': [54, 24],
        'Saudi Arabia': [45, 24],
        'South Africa': [24, -30],
        'Egypt': [30, 27],
        'Nigeria': [8, 9],
        'Kenya': [38, 1],
        'India': [79, 22],
        'Pakistan': [69, 30],
        'China': [104, 35],
        'Japan': [138, 36],
        'South Korea': [128, 36],
        'Singapore': [104, 1],
        'Thailand': [101, 15],
        'Indonesia': [118, -2],
        'Philippines': [122, 13],
        'Australia': [134, -25],
        'New Zealand': [174, -41],
      };

      const section =
        element('section');

      section.className =
        'overflow-hidden rounded-lg border border-border bg-card';

      const heading =
        element(
          'h3',
          'Countries'
        );

      heading.className =
        'border-b border-border px-4 py-2.5 font-semibold';

      const body =
        element('div');

      body.className =
        'grid gap-4 p-4 xl:grid-cols-[minmax(0,2fr)_minmax(220px,0.8fr)]';

      const ns =
        'http://www.w3.org/2000/svg';

      const map =
        document.createElementNS(
          ns,
          'svg'
        );

      map.setAttribute(
        'viewBox',
        '0 0 960 460'
      );

      map.setAttribute(
        'role',
        'img'
      );

      map.setAttribute(
        'aria-label',
        'World map showing analytics users by country'
      );

      map.style.width =
        '100%';

      map.style.display =
        'block';

      const project =
        (lon, lat) => [
          (
            lon + 180
          ) / 360 * 960,
          (
            90 - lat
          ) / 180 * 460,
        ];

      const continentShapes = [
        [
          [-168,72],[-140,70],[-125,58],[-110,52],
          [-95,50],[-82,43],[-65,47],[-55,55],
          [-60,38],[-80,25],[-97,18],[-110,25],
          [-120,34],[-132,50],[-160,58],
        ],
        [
          [-82,12],[-70,8],[-60,-5],[-52,-15],
          [-48,-28],[-58,-42],[-68,-55],[-76,-38],
          [-80,-20],
        ],
        [
          [-11,36],[0,44],[15,48],[28,45],
          [40,50],[52,58],[66,60],[88,72],
          [115,70],[145,58],[170,52],[150,40],
          [120,30],[105,18],[80,8],[60,22],
          [42,35],[28,38],[14,35],
        ],
        [
          [-18,35],[0,37],[20,32],[32,20],
          [42,5],[38,-15],[25,-34],[12,-36],
          [2,-25],[-8,-5],[-15,15],
        ],
        [
          [112,-10],[130,-12],[145,-20],[153,-32],
          [143,-43],[124,-38],[114,-28],
        ],
        [
          [-52,82],[-25,80],[-18,70],[-42,60],
          [-58,68],
        ],
      ];

      const shapeGroup =
        document.createElementNS(
          ns,
          'g'
        );

      for (
        const polygon
        of continentShapes
      ) {
        const points =
          polygon
            .map(([lon, lat]) => {
              const [x, y] =
                project(lon, lat);

              return `${x},${y}`;
            })
            .join(' ');

        const shape =
          document.createElementNS(
            ns,
            'polygon'
          );

        shape.setAttribute(
          'points',
          points
        );

        shape.setAttribute(
          'fill',
          'currentColor'
        );

        shape.setAttribute(
          'opacity',
          '0.12'
        );

        shape.setAttribute(
          'stroke',
          'currentColor'
        );

        shape.setAttribute(
          'stroke-opacity',
          '0.18'
        );

        shape.setAttribute(
          'stroke-width',
          '1'
        );

        shapeGroup.append(shape);
      }

      map.append(shapeGroup);

      const maxUsers =
        Math.max(
          1,
          ...items.map(
            (item) =>
              item.users
          )
        );

      for (
        const item
        of items
      ) {
        const coordinate =
          coordinates[
            item.country
          ];

        if (!coordinate) {
          continue;
        }

        const [x, y] =
          project(
            coordinate[0],
            coordinate[1]
          );

        const radius =
          5
          + (
              item.users
              / maxUsers
              * 8
            );

        const halo =
          document.createElementNS(
            ns,
            'circle'
          );

        halo.setAttribute(
          'cx',
          String(x)
        );
        halo.setAttribute(
          'cy',
          String(y)
        );
        halo.setAttribute(
          'r',
          String(radius + 6)
        );
        halo.setAttribute(
          'fill',
          '#a855f7'
        );
        halo.setAttribute(
          'opacity',
          '0.16'
        );

        const marker =
          document.createElementNS(
            ns,
            'circle'
          );

        marker.setAttribute(
          'cx',
          String(x)
        );
        marker.setAttribute(
          'cy',
          String(y)
        );
        marker.setAttribute(
          'r',
          String(radius)
        );
        marker.setAttribute(
          'fill',
          '#a855f7'
        );
        marker.setAttribute(
          'stroke',
          '#ffffff'
        );
        marker.setAttribute(
          'stroke-opacity',
          '0.85'
        );
        marker.setAttribute(
          'stroke-width',
          '1.5'
        );

        const title =
          document.createElementNS(
            ns,
            'title'
          );

        title.textContent =
          `${item.country}: ${formatInteger(item.users)} users, ${formatInteger(item.sessions)} sessions`;

        marker.append(title);

        map.append(
          halo,
          marker
        );
      }

      const list =
        element('div');

      list.className =
        'space-y-1 self-start';

      for (
        const item
        of items
      ) {
        const row =
          element('div');

        row.className =
          'grid grid-cols-[minmax(0,1fr)_auto] gap-3 rounded-md px-2 py-2 text-sm hover:bg-muted/40';

        const country =
          element(
            'span',
            item.country
          );

        country.className =
          'truncate font-medium';

        const metrics =
          element('div');

        metrics.className =
          'text-right';

        const users =
          element(
            'strong',
            `${formatInteger(item.users)} users`
          );

        users.className =
          'block tabular-nums';

        const sessions =
          element(
            'span',
            `${formatInteger(item.sessions)} sessions`
          );

        sessions.className =
          'block text-xs text-muted-foreground';

        metrics.append(
          users,
          sessions
        );

        row.append(
          country,
          metrics
        );

        list.append(row);
      }

      body.append(
        map,
        list
      );

      section.append(
        heading,
        body
      );

      root.append(section);
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
