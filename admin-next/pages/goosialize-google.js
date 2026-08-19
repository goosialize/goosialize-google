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
              [
                'Clicks',
                formatInteger(
                  metrics.clicks
                ),
              ],
              [
                'Impressions',
                formatInteger(
                  metrics.impressions
                ),
              ],
              [
                'CTR',
                formatPercent(
                  metrics.ctr
                ),
              ],
              [
                'Average position',
                formatDecimal(
                  metrics.position,
                  1
                ),
              ],
            ]
          : [
              [
                'Active users',
                formatInteger(
                  metrics.activeUsers
                ),
                '👥',
                '#a855f7',
              ],
              [
                'New users',
                formatInteger(
                  metrics.newUsers
                ),
                '✚',
                '#8b5cf6',
              ],
              [
                'Sessions',
                formatInteger(
                  metrics.sessions
                ),
                '↻',
                '#3b82f6',
              ],
              [
                'Views',
                formatInteger(
                  metrics.screenPageViews
                ),
                '◉',
                '#06b6d4',
              ],
              [
                'Engagement',
                formatPercent(
                  metrics.engagementRate
                ),
                '◆',
                '#22c55e',
              ],
              [
                'Avg. session',
                formatDuration(
                  metrics.averageSessionDuration
                ),
                '◷',
                '#f59e0b',
              ],
              [
                'Events',
                formatInteger(
                  metrics.eventCount
                ),
                '⚡',
                '#ec4899',
              ],
              [
                'Key events',
                formatInteger(
                  metrics.keyEvents
                ),
                '★',
                '#f97316',
              ],
            ];

      for (
        const definition
        of definitions
      ) {
        const [
          label,
          value,
          icon = '•',
          accent = '#8b5cf6',
        ] = definition;

        const card =
          element('article');

        card.className =
          'relative overflow-hidden rounded-lg border border-border bg-card p-5';

        card.style.borderTopColor =
          accent;

        card.style.borderTopWidth =
          '2px';

        const top =
          element('div');

        top.className =
          'flex items-center justify-between gap-3';

        const labelNode =
          element(
            'div',
            label
          );

        labelNode.className =
          'text-sm text-muted-foreground';

        const iconNode =
          element(
            'span',
            icon
          );

        iconNode.className =
          'inline-flex h-8 w-8 items-center justify-center rounded-md text-sm font-semibold';

        iconNode.style.color =
          accent;

        iconNode.style.background =
          `${accent}18`;

        top.append(
          labelNode,
          iconNode
        );

        const valueNode =
          element(
            'strong',
            value
          );

        valueNode.className =
          'mt-2 block text-2xl font-semibold tabular-nums';

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
        'grid gap-4 xl:grid-cols-2';

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
          'Traffic channels',
          this.data.traffic_channels,
          [['sessionDefaultChannelGroup', 'Channel']],
          [
            ['sessions', 'Sessions'],
            ['activeUsers', 'Users'],
          ]
        );

        this.appendTable(
          tables,
          'Devices',
          this.data.devices,
          [['deviceCategory', 'Device']],
          [
            ['sessions', 'Sessions'],
            ['activeUsers', 'Users'],
          ]
        );

        this.appendTable(
          tables,
          'Countries',
          this.data.countries,
          [['country', 'Country']],
          [
            ['activeUsers', 'Users'],
            ['sessions', 'Sessions'],
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

        if (
          tables.lastElementChild
        ) {
          tables.lastElementChild
            .classList.add(
              'xl:col-span-2'
            );
        }
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

      for (
        const [label, color]
        of [
          ['Users', '#a855f7'],
          ['Sessions', '#3b82f6'],
          ['Views', '#06b6d4'],
        ]
      ) {
        const item =
          element('span');

        item.className =
          'inline-flex items-center gap-1.5';

        const dot =
          element('span');

        dot.className =
          'h-2 w-2 rounded-full';

        dot.style.background =
          color;

        item.append(
          dot,
          document.createTextNode(
            label
          )
        );

        legend.append(item);
      }

      header.append(
        heading,
        legend
      );

      const chart =
        document.createElementNS(
          'http://www.w3.org/2000/svg',
          'svg'
        );

      chart.setAttribute(
        'viewBox',
        '0 0 900 240'
      );

      chart.setAttribute(
        'role',
        'img'
      );

      chart.setAttribute(
        'aria-label',
        'Users, sessions and views trend'
      );

      chart.style.width =
        '100%';

      chart.style.height =
        'auto';

      chart.style.display =
        'block';

      const width = 900;
      const height = 240;
      const left = 44;
      const right = 18;
      const top = 20;
      const bottom = 34;

      const plotWidth =
        width - left - right;

      const plotHeight =
        height - top - bottom;

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

        const line =
          document.createElementNS(
            'http://www.w3.org/2000/svg',
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
          'opacity',
          '0.10'
        );

        chart.append(line);
      }

      const series = [
        ['users', '#a855f7'],
        ['sessions', '#3b82f6'],
        ['views', '#06b6d4'],
      ];

      for (
        const [key, color]
        of series
      ) {
        const points =
          normalized.map(
            (row, index) => {
              const x =
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

              const value =
                Number(
                  row[key]
                  || 0
                );

              const y =
                top
                + plotHeight
                - (
                    value
                    / maxValue
                    * plotHeight
                  );

              return `${x},${y}`;
            }
          );

        const polyline =
          document.createElementNS(
            'http://www.w3.org/2000/svg',
            'polyline'
          );

        polyline.setAttribute(
          'points',
          points.join(' ')
        );
        polyline.setAttribute(
          'fill',
          'none'
        );
        polyline.setAttribute(
          'stroke',
          color
        );
        polyline.setAttribute(
          'stroke-width',
          '3'
        );
        polyline.setAttribute(
          'stroke-linecap',
          'round'
        );
        polyline.setAttribute(
          'stroke-linejoin',
          'round'
        );

        chart.append(polyline);
      }

      const labels =
        element('div');

      labels.className =
        'flex justify-between px-5 pb-4 text-xs text-muted-foreground';

      const first =
        normalized[0]?.date
        || '';

      const last =
        normalized[
          normalized.length - 1
        ]?.date
        || '';

      labels.append(
        element(
          'span',
          first
        ),
        element(
          'span',
          last
        )
      );

      const chartWrap =
        element('div');

      chartWrap.className =
        'px-3 pt-3 text-muted-foreground';

      chartWrap.append(chart);

      section.append(
        header,
        chartWrap,
        labels
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
        'border-b border-border px-4 py-3 font-semibold';

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
          'px-4 py-3 text-left text-sm font-medium';

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
            'px-4 py-3';

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
            'px-4 py-3 text-right tabular-nums';

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
        'border-b border-border px-4 py-3 font-semibold';

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
          'px-4 py-3 text-left text-sm font-medium';

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
            'px-4 py-3';

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
            'px-4 py-3 text-right tabular-nums';

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
