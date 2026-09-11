'use strict';

const fs =
  require('fs');

const source =
  fs.readFileSync(
    'classes/Analytics/Tracking/AnalyticsTrackingInjector.php',
    'utf8'
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

ok(
  source.includes(
    "analytics_storage: 'denied'"
  ),
  'ANALYTICS_DEFAULT_DENIED'
);

ok(
  source.includes(
    "goosialize-google:consent-ready"
  ),
  'CONSENT_READY_LISTENER'
);

ok(
  source.includes(
    "goosialize-google:consent-changed"
  ),
  'CONSENT_CHANGE_LISTENER'
);

ok(
  source.includes(
    "state?.analytics === true"
  ),
  'ANALYTICS_EXPLICIT_GRANT_REQUIRED'
);

ok(
  source.includes(
    "document.createElement("
  )
  && source.includes(
    "'script'"
  ),
  'DYNAMIC_GTAG_LOADER'
);

ok(
  source.includes(
    "googletagmanager.com/gtag/js?id="
  ),
  'GTAG_NETWORK_LOADER_PRESENT'
);

ok(
  !source.includes(
    '<script async src="https://www.googletagmanager.com/gtag/js?id='
  ),
  'NO_STATIC_GTAG_NETWORK_REQUEST'
);

ok(
  source.includes(
    "analytics_storage:\n          next\n            ? 'granted'\n            : 'denied'"
  ),
  'WITHDRAWAL_UPDATES_DENIED'
);

ok(
  source.includes(
    "if (\n      started\n      || granted !== true"
  ),
  'NO_START_WITHOUT_GRANT'
);

console.log(
  'TRACKING_CONSENT_GATE_CONTRACT=PASS'
);
