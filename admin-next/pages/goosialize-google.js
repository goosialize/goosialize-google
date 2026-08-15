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

      this.properties = [];
      this.propertyId = '';
      this.days = 30;
      this.data = null;
      this.loading = false;
      this.error = null;

      this._actionListener =
        (event) => {
          if (
            event?.detail?.id
              === 'refresh'
          ) {
            this.loadDashboard();
          }
        };
    }

    connectedCallback() {
      window.addEventListener(
        'grav:plugin-page-action',
        this._actionListener
      );

      this.render();
      this.load();
    }

    disconnectedCallback() {
      window.removeEventListener(
        'grav:plugin-page-action',
        this._actionListener
      );
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
        const payload =
          await this.apiGet(
            '/goosialize-google/properties'
          );

        this.properties =
          Array.isArray(payload?.data)
            ? payload.data
            : [];

        if (
          !this.propertyId
          && this.properties.length > 0
        ) {
          this.propertyId =
            String(
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
            : 'Google Analytics could not be loaded.';
      } finally {
        this.loading = false;
        this.render();
      }
    }

    async loadDashboard(
      manageState = true
    ) {
      if (!this.propertyId) {
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
          new URLSearchParams({
            property_id:
              this.propertyId,
            days:
              String(this.days),
          });

        this.data =
          await this.apiGet(
            `/goosialize-google/analytics?${query.toString()}`
          );
      } catch (error) {
        this.data = null;

        this.error =
          error instanceof Error
            ? error.message
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

      root.style.display =
        'grid';

      root.style.gap =
        '1.5rem';

      root.style.width =
        '100%';

      root.style.boxSizing =
        'border-box';

      const surfaceBorder =
        'var(--border-color, rgba(255, 255, 255, 0.10))';

      const surfaceBackground =
        'var(--body-bg, rgba(255, 255, 255, 0.025))';

      const mutedColor =
        'var(--text-muted, var(--gray-500, #8f94a3))';

      const primaryColor =
        'var(--primary-color, var(--purple, #8b5cf6))';

      const toolbar =
        element('section');

      toolbar.style.display =
        'flex';

      toolbar.style.alignItems =
        'end';

      toolbar.style.justifyContent =
        'space-between';

      toolbar.style.gap =
        '1rem';

      toolbar.style.flexWrap =
        'wrap';

      toolbar.style.padding =
        '1rem';

      toolbar.style.border =
        `1px solid ${surfaceBorder}`;

      toolbar.style.borderRadius =
        '0.65rem';

      toolbar.style.background =
        surfaceBackground;

      const propertyGroup =
        element('div');

      propertyGroup.style.display =
        'grid';

      propertyGroup.style.gap =
        '0.45rem';

      propertyGroup.style.flex =
        '1 1 320px';

      propertyGroup.style.maxWidth =
        '520px';

      const propertyLabel =
        element(
          'label',
          'Property'
        );

      propertyLabel.style.fontSize =
        '0.78rem';

      propertyLabel.style.fontWeight =
        '600';

      propertyLabel.style.color =
        mutedColor;

      propertyLabel.style.textTransform =
        'uppercase';

      propertyLabel.style.letterSpacing =
        '0.04em';

      const property =
        element('select');

      property.setAttribute(
        'aria-label',
        'GA4 property'
      );

      property.style.width =
        '100%';

      property.style.minHeight =
        '2.65rem';

      property.style.padding =
        '0 2.5rem 0 0.8rem';

      property.style.border =
        `1px solid ${surfaceBorder}`;

      property.style.borderRadius =
        '0.5rem';

      property.style.background =
        'var(--input-bg, var(--body-bg, transparent))';

      property.style.color =
        'inherit';

      property.style.font =
        'inherit';

      property.style.fontWeight =
        '600';

      property.style.cursor =
        this.loading
          ? 'wait'
          : 'pointer';

      property.disabled =
        this.loading;

      if (
        this.properties.length === 0
      ) {
        const option =
          element(
            'option',
            'No accessible properties'
          );

        option.value = '';

        property.append(option);

        property.disabled = true;
      } else {
        for (
          const item
          of this.properties
        ) {
          const option =
            element(
              'option',
              `${item.property_name} — ${item.account_name}`
            );

          option.value =
            String(
              item.property_id
            );

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

      periodGroup.style.display =
        'grid';

      periodGroup.style.gap =
        '0.45rem';

      const periodLabel =
        element(
          'span',
          'Period'
        );

      periodLabel.style.fontSize =
        '0.78rem';

      periodLabel.style.fontWeight =
        '600';

      periodLabel.style.color =
        mutedColor;

      periodLabel.style.textTransform =
        'uppercase';

      periodLabel.style.letterSpacing =
        '0.04em';

      const periodControls =
        element('div');

      periodControls.setAttribute(
        'role',
        'group'
      );

      periodControls.setAttribute(
        'aria-label',
        'Analytics period'
      );

      periodControls.style.display =
        'inline-flex';

      periodControls.style.padding =
        '0.2rem';

      periodControls.style.gap =
        '0.2rem';

      periodControls.style.border =
        `1px solid ${surfaceBorder}`;

      periodControls.style.borderRadius =
        '0.55rem';

      periodControls.style.background =
        'var(--input-bg, rgba(0, 0, 0, 0.08))';

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

        button.setAttribute(
          'aria-pressed',
          active
            ? 'true'
            : 'false'
        );

        button.style.minHeight =
          '2.2rem';

        button.style.padding =
          '0 0.8rem';

        button.style.border =
          '0';

        button.style.borderRadius =
          '0.4rem';

        button.style.font =
          'inherit';

        button.style.fontSize =
          '0.9rem';

        button.style.fontWeight =
          active
            ? '700'
            : '500';

        button.style.cursor =
          this.loading
            ? 'wait'
            : 'pointer';

        button.style.color =
          active
            ? 'var(--primary-contrast, #fff)'
            : 'inherit';

        button.style.background =
          active
            ? primaryColor
            : 'transparent';

        button.style.opacity =
          this.loading
            ? '0.65'
            : '1';

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

      toolbar.append(
        propertyGroup,
        periodGroup
      );

      root.append(toolbar);

      if (this.loading) {
        const loadingPanel =
          element('section');

        loadingPanel.style.padding =
          '2.5rem 1.5rem';

        loadingPanel.style.border =
          `1px solid ${surfaceBorder}`;

        loadingPanel.style.borderRadius =
          '0.65rem';

        loadingPanel.style.background =
          surfaceBackground;

        loadingPanel.style.textAlign =
          'center';

        const loadingTitle =
          element(
            'strong',
            'Loading Google Analytics…'
          );

        loadingTitle.style.display =
          'block';

        loadingTitle.style.fontSize =
          '1rem';

        loadingPanel.append(
          loadingTitle
        );

        root.append(
          loadingPanel
        );

        this.append(root);

        return;
      }

      if (this.error) {
        const errorPanel =
          element('section');

        errorPanel.setAttribute(
          'role',
          'alert'
        );

        errorPanel.style.padding =
          '1rem 1.1rem';

        errorPanel.style.border =
          '1px solid var(--danger-color, #d95858)';

        errorPanel.style.borderRadius =
          '0.65rem';

        errorPanel.style.background =
          'var(--danger-bg, rgba(217, 88, 88, 0.08))';

        const errorTitle =
          element(
            'strong',
            'Google Analytics could not be loaded'
          );

        errorTitle.style.display =
          'block';

        errorTitle.style.marginBottom =
          '0.35rem';

        const errorMessage =
          element(
            'div',
            this.error
          );

        errorMessage.style.color =
          mutedColor;

        errorPanel.append(
          errorTitle,
          errorMessage
        );

        root.append(
          errorPanel
        );

        this.append(root);

        return;
      }

      if (
        this.properties.length === 0
      ) {
        const noProperties =
          element('section');

        noProperties.style.padding =
          '2rem 1.5rem';

        noProperties.style.border =
          `1px solid ${surfaceBorder}`;

        noProperties.style.borderRadius =
          '0.65rem';

        noProperties.style.background =
          surfaceBackground;

        noProperties.append(
          element(
            'strong',
            'No accessible Google Analytics properties'
          )
        );

        const text =
          element(
            'p',
            'The configured Google account does not currently expose any GA4 properties.'
          );

        text.style.margin =
          '0.5rem 0 0';

        text.style.color =
          mutedColor;

        noProperties.append(text);

        root.append(noProperties);

        this.append(root);

        return;
      }

      if (!this.data) {
        this.append(root);

        return;
      }

      const heading =
        element('section');

      heading.style.display =
        'flex';

      heading.style.alignItems =
        'end';

      heading.style.justifyContent =
        'space-between';

      heading.style.gap =
        '1rem';

      heading.style.flexWrap =
        'wrap';

      const headingCopy =
        element('div');

      const eyebrow =
        element(
          'div',
          this.data?.property?.account
            || 'Google Analytics'
        );

      eyebrow.style.marginBottom =
        '0.3rem';

      eyebrow.style.fontSize =
        '0.78rem';

      eyebrow.style.fontWeight =
        '600';

      eyebrow.style.color =
        mutedColor;

      eyebrow.style.textTransform =
        'uppercase';

      eyebrow.style.letterSpacing =
        '0.04em';

      const title =
        element(
          'h2',
          this.data?.property?.name
            || 'Google Analytics'
        );

      title.style.margin =
        '0';

      title.style.fontSize =
        '1.45rem';

      title.style.lineHeight =
        '1.2';

      const period =
        element(
          'p',
          `${this.data?.period?.start_date || ''} — ${this.data?.period?.end_date || ''}`
        );

      period.style.margin =
        '0.35rem 0 0';

      period.style.color =
        mutedColor;

      headingCopy.append(
        eyebrow,
        title,
        period
      );

      heading.append(
        headingCopy
      );

      root.append(
        heading
      );

      if (
        this.data.empty === true
      ) {
        const empty =
          element('section');

        empty.style.display =
          'grid';

        empty.style.placeItems =
          'center';

        empty.style.minHeight =
          '260px';

        empty.style.padding =
          '2.5rem 1.5rem';

        empty.style.border =
          `1px solid ${surfaceBorder}`;

        empty.style.borderRadius =
          '0.75rem';

        empty.style.background =
          surfaceBackground;

        empty.style.textAlign =
          'center';

        const emptyInner =
          element('div');

        emptyInner.style.maxWidth =
          '520px';

        const emptyMark =
          element(
            'div',
            '↗'
          );

        emptyMark.setAttribute(
          'aria-hidden',
          'true'
        );

        emptyMark.style.display =
          'grid';

        emptyMark.style.placeItems =
          'center';

        emptyMark.style.width =
          '3rem';

        emptyMark.style.height =
          '3rem';

        emptyMark.style.margin =
          '0 auto 1rem';

        emptyMark.style.borderRadius =
          '0.7rem';

        emptyMark.style.background =
          'var(--primary-bg, rgba(139, 92, 246, 0.14))';

        emptyMark.style.color =
          primaryColor;

        emptyMark.style.fontSize =
          '1.35rem';

        emptyMark.style.fontWeight =
          '700';

        const emptyTitle =
          element(
            'h3',
            'No analytics data'
          );

        emptyTitle.style.margin =
          '0';

        emptyTitle.style.fontSize =
          '1.15rem';

        const emptyText =
          element(
            'p',
            'No analytics data for this period.'
          );

        emptyText.style.margin =
          '0.55rem 0 0';

        emptyText.style.color =
          mutedColor;

        emptyText.style.lineHeight =
          '1.55';

        const emptyHelp =
          element(
            'p',
            'The Google Analytics connection is working correctly. Try another period or confirm that this GA4 property is receiving traffic.'
          );

        emptyHelp.style.margin =
          '0.35rem 0 0';

        emptyHelp.style.color =
          mutedColor;

        emptyHelp.style.fontSize =
          '0.88rem';

        emptyHelp.style.lineHeight =
          '1.55';

        emptyInner.append(
          emptyMark,
          emptyTitle,
          emptyText,
          emptyHelp
        );

        empty.append(
          emptyInner
        );

        root.append(empty);

        this.append(root);

        return;
      }

      const metrics =
        this.data.overview
        || {};

      const cards =
        element('section');

      cards.setAttribute(
        'aria-label',
        'Analytics overview'
      );

      cards.style.display =
        'grid';

      cards.style.gridTemplateColumns =
        'repeat(auto-fit, minmax(170px, 1fr))';

      cards.style.gap =
        '0.8rem';

      const definitions = [
        [
          'Active users',
          formatInteger(
            metrics.activeUsers
          ),
        ],
        [
          'New users',
          formatInteger(
            metrics.newUsers
          ),
        ],
        [
          'Sessions',
          formatInteger(
            metrics.sessions
          ),
        ],
        [
          'Views',
          formatInteger(
            metrics.screenPageViews
          ),
        ],
        [
          'Engagement',
          formatPercent(
            metrics.engagementRate
          ),
        ],
        [
          'Avg. session',
          formatDuration(
            metrics.averageSessionDuration
          ),
        ],
        [
          'Events',
          formatInteger(
            metrics.eventCount
          ),
        ],
        [
          'Key events',
          formatInteger(
            metrics.keyEvents
          ),
        ],
      ];

      for (
        const [label, value]
        of definitions
      ) {
        const card =
          element('article');

        card.style.padding =
          '1rem 1.05rem';

        card.style.border =
          `1px solid ${surfaceBorder}`;

        card.style.borderRadius =
          '0.65rem';

        card.style.background =
          surfaceBackground;

        const labelNode =
          element(
            'div',
            label
          );

        labelNode.style.marginBottom =
          '0.45rem';

        labelNode.style.fontSize =
          '0.78rem';

        labelNode.style.fontWeight =
          '600';

        labelNode.style.color =
          mutedColor;

        labelNode.style.textTransform =
          'uppercase';

        labelNode.style.letterSpacing =
          '0.035em';

        const valueNode =
          element(
            'strong',
            value
          );

        valueNode.style.display =
          'block';

        valueNode.style.fontSize =
          '1.65rem';

        valueNode.style.lineHeight =
          '1.1';

        valueNode.style.fontVariantNumeric =
          'tabular-nums';

        card.append(
          labelNode,
          valueNode
        );

        cards.append(card);
      }

      root.append(cards);

      const tables =
        element('section');

      tables.style.display =
        'grid';

      tables.style.gridTemplateColumns =
        'repeat(auto-fit, minmax(min(100%, 420px), 1fr))';

      tables.style.gap =
        '1rem';

      this.appendTable(
        tables,
        'Top pages',
        this.data.top_pages,
        [
          ['pagePath', 'Page'],
        ],
        [
          ['screenPageViews', 'Views'],
          ['activeUsers', 'Users'],
        ]
      );

      this.appendTable(
        tables,
        'Traffic channels',
        this.data.traffic_channels,
        [
          [
            'sessionDefaultChannelGroup',
            'Channel',
          ],
        ],
        [
          ['sessions', 'Sessions'],
          ['activeUsers', 'Users'],
        ]
      );

      this.appendTable(
        tables,
        'Devices',
        this.data.devices,
        [
          ['deviceCategory', 'Device'],
        ],
        [
          ['sessions', 'Sessions'],
          ['activeUsers', 'Users'],
        ]
      );

      this.appendTable(
        tables,
        'Countries',
        this.data.countries,
        [
          ['country', 'Country'],
        ],
        [
          ['activeUsers', 'Users'],
          ['sessions', 'Sessions'],
        ]
      );

      this.appendTable(
        tables,
        'Events',
        this.data.events,
        [
          ['eventName', 'Event'],
        ],
        [
          ['eventCount', 'Count'],
          ['totalUsers', 'Users'],
        ]
      );

      if (tables.childElementCount > 0) {
        root.append(tables);
      }

      this.append(root);
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

      section.style.minWidth =
        '0';

      section.style.border =
        '1px solid var(--border-color, rgba(255, 255, 255, 0.10))';

      section.style.borderRadius =
        '0.65rem';

      section.style.background =
        'var(--body-bg, rgba(255, 255, 255, 0.025))';

      section.style.overflow =
        'hidden';

      const heading =
        element(
          'h3',
          title
        );

      heading.style.margin =
        '0';

      heading.style.padding =
        '0.9rem 1rem';

      heading.style.fontSize =
        '1rem';

      heading.style.borderBottom =
        '1px solid var(--border-color, rgba(255, 255, 255, 0.10))';

      section.append(heading);

      const scroll =
        element('div');

      scroll.style.overflowX =
        'auto';

      const table =
        element('table');

      table.style.width =
        '100%';

      table.style.borderCollapse =
        'collapse';

      table.style.fontSize =
        '0.88rem';

      const head =
        element('thead');

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

        th.style.padding =
          '0.65rem 0.8rem';

        th.style.textAlign =
          'left';

        th.style.whiteSpace =
          'nowrap';

        th.style.fontSize =
          '0.72rem';

        th.style.fontWeight =
          '700';

        th.style.textTransform =
          'uppercase';

        th.style.letterSpacing =
          '0.035em';

        th.style.color =
          'var(--text-muted, var(--gray-500, #8f94a3))';

        th.style.borderBottom =
          '1px solid var(--border-color, rgba(255, 255, 255, 0.10))';

        headRow.append(th);
      }

      head.append(headRow);

      table.append(head);

      const body =
        element('tbody');

      for (const row of rows) {
        const tr =
          element('tr');

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

          td.style.padding =
            '0.7rem 0.8rem';

          td.style.borderBottom =
            '1px solid var(--border-color, rgba(255, 255, 255, 0.07))';

          td.style.verticalAlign =
            'top';

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

          td.style.padding =
            '0.7rem 0.8rem';

          td.style.borderBottom =
            '1px solid var(--border-color, rgba(255, 255, 255, 0.07))';

          td.style.textAlign =
            'right';

          td.style.whiteSpace =
            'nowrap';

          td.style.fontVariantNumeric =
            'tabular-nums';

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
