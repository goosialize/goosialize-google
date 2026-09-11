(() => {
  'use strict';

  const READY_EVENT =
    'goosialize-google:consent-ready';

  const CHANGE_EVENT =
    'goosialize-google:consent-changed';

  let initialized = false;
  let unsubscribeReady = null;
  let unsubscribeChange = null;

  function consumer() {
    return window.GoosializeConsentConsumer
      ?? null;
  }

  function stateFromSnapshot(snapshot) {
    const categories =
      snapshot?.categories ?? {};

    return {
      analytics:
        categories.analytics === true,

      marketing:
        categories.marketing === true
    };
  }

  function getState() {
    const api = consumer();

    if (
      !api
      || typeof api.getSnapshot
        !== 'function'
    ) {
      return {
        analytics: false,
        marketing: false
      };
    }

    return stateFromSnapshot(
      api.getSnapshot()
    );
  }

  function hasAnalyticsConsent() {
    return getState()
      .analytics === true;
  }

  function hasMarketingConsent() {
    return getState()
      .marketing === true;
  }

  function dispatch(
    name,
    detail
  ) {
    window.dispatchEvent(
      new CustomEvent(
        name,
        {
          detail
        }
      )
    );
  }

  function init() {
    if (initialized) {
      return;
    }

    const api =
      consumer();

    if (!api) {
      dispatch(
        READY_EVENT,
        {
          current: {
            analytics: false,
            marketing: false
          },
          source:
            'consent-consumer-unavailable'
        }
      );

      initialized = true;
      return;
    }

    if (
      typeof api.whenReady
      === 'function'
    ) {
      unsubscribeReady =
        api.whenReady(
          snapshot => {
            dispatch(
              READY_EVENT,
              {
                current:
                  stateFromSnapshot(
                    snapshot
                  ),
                source:
                  'goosialize-consent'
              }
            );
          }
        );
    }

    if (
      typeof api.onChange
      === 'function'
    ) {
      unsubscribeChange =
        api.onChange(
          (
            current,
            previous
          ) => {
            dispatch(
              CHANGE_EVENT,
              {
                current:
                  stateFromSnapshot(
                    current
                  ),

                previous:
                  stateFromSnapshot(
                    previous
                  ),

                source:
                  'goosialize-consent'
              }
            );
          }
        );
    }

    initialized = true;
  }

  function destroy() {
    if (
      typeof unsubscribeReady
      === 'function'
    ) {
      unsubscribeReady();
    }

    if (
      typeof unsubscribeChange
      === 'function'
    ) {
      unsubscribeChange();
    }

    unsubscribeReady = null;
    unsubscribeChange = null;
    initialized = false;
  }

  window.GoosializeGoogleConsent =
    Object.freeze({
      init,
      destroy,
      getState,
      hasAnalyticsConsent,
      hasMarketingConsent
    });

  init();
})();
