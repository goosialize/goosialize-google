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
        '1.25rem';

      const controls =
        element('div');

      controls.style.display =
        'flex';

      controls.style.gap =
        '0.75rem';

      controls.style.flexWrap =
        'wrap';

      controls.style.alignItems =
        'center';

      const property =
        element('select');

      property.setAttribute(
        'aria-label',
        'GA4 property'
      );

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

      controls.append(property);

      for (
        const days
        of [7, 30, 90]
      ) {
        const button =
          element(
            'button',
            `${days} days`
          );

        button.type =
          'button';

        button.disabled =
          this.loading
          || days === this.days;

        button.addEventListener(
          'click',
          () => {
            this.days = days;
            this.loadDashboard();
          }
        );

        controls.append(button);
      }

      root.append(controls);

      if (this.loading) {
        root.append(
          element(
            'p',
            'Loading Google Analytics…'
          )
        );

        this.append(root);
        return;
      }

      if (this.error) {
        const error =
          element(
            'p',
            this.error
          );

        error.setAttribute(
          'role',
          'alert'
        );

        root.append(error);
        this.append(root);
        return;
      }

      if (
        this.properties.length === 0
      ) {
        root.append(
          element(
            'p',
            'No accessible Google Analytics properties were found.'
          )
        );

        this.append(root);
        return;
      }

      if (!this.data) {
        root.append(
          element(
            'p',
            'Select a Google Analytics property.'
          )
        );

        this.append(root);
        return;
      }

      const heading =
        element(
          'div'
        );

      const title =
        element(
          'h2',
          this.data?.property?.name
            || 'Google Analytics'
        );

      const period =
        element(
          'p',
          `${this.data?.period?.start_date || ''} — ${this.data?.period?.end_date || ''}`
        );

      heading.append(
        title,
        period
      );

      root.append(heading);

      if (
        this.data.empty === true
      ) {
        root.append(
          element(
            'p',
            'No analytics data for this period.'
          )
        );

        this.append(root);
        return;
      }

      const metrics =
        this.data.overview
        || {};

      const cards =
        element('div');

      cards.style.display =
        'grid';

      cards.style.gridTemplateColumns =
        'repeat(auto-fit, minmax(150px, 1fr))';

      cards.style.gap =
        '0.75rem';

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
          '1rem';

        card.style.border =
          '1px solid var(--border-color, #ddd)';

        card.style.borderRadius =
          '0.5rem';

        const valueNode =
          element(
            'strong',
            value
          );

        valueNode.style.display =
          'block';

        valueNode.style.fontSize =
          '1.5rem';

        card.append(
          valueNode,
          element('span', label)
        );

        cards.append(card);
      }

      root.append(cards);

      this.appendTable(
        root,
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
        root,
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
        root,
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
        root,
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
        root,
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
        element('section');

      section.append(
        element('h3', title)
      );

      const table =
        element('table');

      table.style.width =
        '100%';

      const head =
        element('thead');

      const headRow =
        element('tr');

      for (
        const [, label]
        of [...dimensions, ...metrics]
      ) {
        headRow.append(
          element('th', label)
        );
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
          tr.append(
            element(
              'td',
              row?.dimensions?.[key]
              ?? ''
            )
          );
        }

        for (
          const [key]
          of metrics
        ) {
          tr.append(
            element(
              'td',
              formatInteger(
                row?.metrics?.[key]
              )
            )
          );
        }

        body.append(tr);
      }

      table.append(body);
      section.append(table);
      root.append(section);
    }
  }

  customElements.define(
    TAG,
    GoosializeGooglePage
  );
})();
