'use strict';

const fs =
  require('fs');

const vm =
  require('vm');

const source =
  fs.readFileSync(
    'assets/js/consent-consumer.js',
    'utf8'
  );

const listeners =
  new Map();

class CustomEvent {
  constructor(
    type,
    options = {}
  ) {
    this.type = type;
    this.detail =
      options.detail;
  }
}

function addEventListener(
  type,
  listener
) {
  if (!listeners.has(type)) {
    listeners.set(
      type,
      []
    );
  }

  listeners
    .get(type)
    .push(listener);
}

function dispatchEvent(event) {
  for (
    const listener
    of listeners.get(
      event.type
    ) ?? []
  ) {
    listener(event);
  }
}

let snapshot = null;
let readyCallback = null;
let changeCallback = null;

const window = {
  addEventListener,
  dispatchEvent,

  GoosializeConsentConsumer: {
    getSnapshot() {
      return snapshot;
    },

    whenReady(callback) {
      readyCallback =
        callback;

      return () => {
        readyCallback = null;
      };
    },

    onChange(callback) {
      changeCallback =
        callback;

      return () => {
        changeCallback = null;
      };
    }
  }
};

const context =
  vm.createContext({
    window,
    CustomEvent,
    console
  });

vm.runInContext(
  source,
  context,
  {
    filename:
      'consent-consumer.js'
  }
);

function ok(
  condition,
  label
) {
  if (!condition) {
    throw new Error(
      `FAIL=${label}`
    );
  }

  console.log(
    `${label}=PASS`
  );
}

const adapter =
  window.GoosializeGoogleConsent;

ok(
  adapter
  && typeof adapter === 'object',
  'GOOGLE_CONSENT_ADAPTER_AVAILABLE'
);

ok(
  adapter.hasAnalyticsConsent()
    === false,
  'UNKNOWN_ANALYTICS_FAIL_CLOSED'
);

ok(
  adapter.hasMarketingConsent()
    === false,
  'UNKNOWN_MARKETING_FAIL_CLOSED'
);

let readyEvent = null;
let changeEvent = null;

window.addEventListener(
  'goosialize-google:consent-ready',
  event => {
    readyEvent = event.detail;
  }
);

window.addEventListener(
  'goosialize-google:consent-changed',
  event => {
    changeEvent = event.detail;
  }
);

snapshot = {
  categories: {
    necessary: true,
    preferences: false,
    analytics: true,
    marketing: false
  }
};

readyCallback(snapshot);

ok(
  readyEvent
  && readyEvent.current.analytics
    === true
  && readyEvent.current.marketing
    === false,
  'READY_EVENT_MAPPING'
);

ok(
  adapter.hasAnalyticsConsent()
    === true,
  'ANALYTICS_GRANTED'
);

ok(
  adapter.hasMarketingConsent()
    === false,
  'MARKETING_DENIED'
);

const previous =
  snapshot;

snapshot = {
  categories: {
    necessary: true,
    preferences: false,
    analytics: false,
    marketing: true
  }
};

changeCallback(
  snapshot,
  previous
);

ok(
  changeEvent.current.analytics
    === false
  && changeEvent.current.marketing
    === true,
  'CHANGE_CURRENT_MAPPING'
);

ok(
  changeEvent.previous.analytics
    === true
  && changeEvent.previous.marketing
    === false,
  'CHANGE_PREVIOUS_MAPPING'
);

ok(
  adapter.hasAnalyticsConsent()
    === false,
  'ANALYTICS_WITHDRAWAL_DENIED'
);

ok(
  adapter.hasMarketingConsent()
    === true,
  'MARKETING_GRANTED'
);

adapter.destroy();

ok(
  readyCallback === null
  && changeCallback === null,
  'CONSUMER_UNSUBSCRIBE=PASS'
);

const banned = [
  'document.cookie',
  'localStorage',
  'sessionStorage',
  'indexedDB',
  'acceptAll',
  'rejectAll',
  'savePreferences',
  'googletagmanager.com',
  'google-analytics.com',
  'gtag(',
  'dataLayer'
];

for (const token of banned) {
  ok(
    !source.includes(token),
    `BOUNDARY_${token.replace(
      /[^A-Za-z0-9]+/g,
      '_'
    )}`
  );
}

console.log(
  'GOOGLE_CONSENT_CONSUMER_ACCEPTANCE=PASS'
);
