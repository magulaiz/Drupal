/**
 * @file
 * Connect Drupal javascript to htmx inserted content.
 */

// Drupal core standard practice: wrap JS in an anonymous closure.
(function (Drupal, drupalSettings, once, htmx, loadjs) {
  // Enforce the declared dependency on HTMX.
  if (typeof htmx !== 'object' || !htmx.hasOwnProperty('process')) {
    return;
  }

  /**
   * Attaches Drupal behaviors to markup inserted by HTMX.
   *
   * @param {Event} htmxLoadEvent
   *   Event returned from HTMX loading processes.
   */
  function htmxDrupalBehaviors(htmxLoadEvent) {
    // The attachBehaviors method searches within the context.
    // We need to go up one level so that the loaded content is processed
    // completely.
    Drupal.attachBehaviors(htmxLoadEvent.detail.elt?.parentElement, drupalSettings);
  }


  /**
   * Includes Drupal data with HTMX requests.
   *
   * @param {Event} configRequestEvent
   *   The event passed from HTMX as it configures a request.
   */
  function htmxDrupalData(configRequestEvent) {
    const url = new URL(configRequestEvent.detail.path, document.location.href);
    const origin = document.location.origin;
    const sameHost = origin === url.origin;
    if (sameHost) {
      // We only need to add this data for htmx requests back to the site.
      configRequestEvent.detail.parameters['ajax_page_state[libraries]'] = drupalSettings.ajaxPageState.libraries;
      // Swap in drupal data selectors as #id values are altered to be unique.
      configRequestEvent.detail.headers['HX-Target'] = configRequestEvent.detail.target.dataset.drupalSelector;
      configRequestEvent.detail.headers['HX-Trigger'] = configRequestEvent.detail.elt.dataset.drupalSelector;
    }
  }

  /**
   * Processes asset information from the response to an HTMX request.
   *
   * @param {Event} assetsAppendedEvent
   *   The event triggered by header from HtmxResponseAttachmentsProcessor.
   */
  function htmxDrupalAssetProcessor(assetsAppendedEvent) {

    /**
     * Local helper function to merge two objects recursively.
     *
     * @param current
     *   The object to receive the merged values.
     * @param sources
     *   The objects to merge into current.
     *
     * @see https://youmightnotneedjquery.com/#deep_extend
     */
    function mergeSettings(current, ...sources) {
      if (!current) {
        return {};
      }

      for (const obj of sources) {
        if (!obj) {
          continue;
        }

        for (const [key, value] of Object.entries(obj)) {
          switch (Object.prototype.toString.call(value)) {
            case '[object Object]':
              current[key] = current[key] || {};
              current[key] = mergeSettings(current[key], value);
              break;

            case '[object Array]':
              current[key] = mergeSettings(new Array(value.length), value);
              break;

            default:
              current[key] = value;
          }
        }
      }

      return current;
    }

    // Find any inserted assets.
    const assetsTag = oobSwapEvent.detail.target.querySelector('script[data-drupal-selector="drupal-htmx-assets"]');
    if (!(assetsTag instanceof HTMLElement)) {
      return;
    }
    // Parse assets and initialize container variables.
    const assets = JSON.parse(assetsTag.textContent);
    let cssItems = [];
    let scriptItems = [];
    let scriptBottomItems = [];
    let paths = [];
    if (assets.constructor.name === 'Object') {
      // Variable assets should have properties 'styles', 'scripts,
      // 'scripts_bottom', 'settings'. See
      // HtmxResponseAttachmentsProcessor::processAssetLibraries
      cssItems = new Map(assets['styles'].map((item) => [`css!${item['#attributes']['href']}`, item]));
      scriptItems = new Map(assets['scripts'].map((item) => [item['#attributes']['src'], item]));
      scriptBottomItems = new Map(assets['scripts_bottom'].map((item) => [item['#attributes']['src'], item]));
      mergeSettings(drupalSettings, assets['settings']);
    }
    // Load CSS.
    if (cssItems instanceof Map && cssItems.size > 0) {
      // Fetch in parallel but load in sequence.
      cssItems.forEach((value, key) => paths.push(key));
      loadjs(paths, {
        async: false,
        before: function (path, linkElement) {
          const item = cssItems.get(path);
          for (const [attributeKey, attributeValue] of Object.entries(item['#attributes'])) {
            linkElement.setAttribute(attributeKey, attributeValue);
          }
        },
      });
    }
    /* By default, loadjs appends the script to the head. When scripts
     * are loaded via HTMX, their location has no impact on
     * functionality. But, since Drupal loaded scripts can choose
     * their parent element, we provide that option here for the sake of
     * consistency.
     */
    if (scriptItems instanceof Map && scriptItems.size > 0) {
      paths = [];
      scriptItems.forEach((value, key) => paths.push(key));
      // Load head JS.
      // Fetch in parallel but load in sequence.
      loadjs(paths, {
        async: false,
        before: function (path, scriptElement) {
          const item = scriptItems.get(path);
          for (const [attributeKey, attributeValue] of Object.entries(item['#attributes'])) {
            scriptElement.setAttribute(attributeKey, attributeValue);
          }
        },
      });
    }
    if (scriptBottomItems instanceof Map && scriptBottomItems.size > 0) {
      paths = [];
      scriptBottomItems.forEach((value, key) => paths.push(key));
      // Fetch in parallel but load in sequence.
      loadjs(paths, {
        async: false,
        before: function (path, scriptElement) {
          const item = scriptBottomItems.get(path);
          for (const [attributeKey, attributeValue] of Object.entries(item['#attributes'])) {
            scriptElement.setAttribute(attributeKey, attributeValue);
          }
          document.body.appendChild(scriptElement);
          // Return false to bypass loadjs' default DOM insertion
          // mechanism.
          return false;
        },
      });
    }
    // Any assets now processed.  Remove the found asset tag.
    htmx.remove(assetsTag);
  }

  /* Initialize listeners. */

  /**
   * Process new content through HTMX.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Process all new content in case any HTMX attributes are in that content.
   */
  Drupal.behaviors.htmxProcess = {
    attach(context) {
      htmx.process(context);
    },
  };

  /**
   * Set listeners for the HTMX events that power our integration.
   *
   * @type {{attach: Drupal.behaviors.htmxBehaviors.attach}}
   */
  Drupal.behaviors.htmxBehaviors = {
    attach: () => {
      // JS should use native methods to enforce a single instance of our event
      // handlers since we used named functions.  Use the `once` library to be
      // consistent with the rest of core.
      if (!once('htmxBehaviors', 'html').length) {
        return;
      }
      // Fired by HTMX before a request is sent.
      window.addEventListener('htmx:configRequest', htmxDrupalData);
      // Fired by HTMX after an 'out of band' content swap, which we use to add
      // asset info from the resonse.
      window.addEventListener('htmx:oobAfterSwap', htmxDrupalAssetProcessor);
      // Attach Drupal behaviors after a normal HTMC content swap.
      window.addEventListener('htmx:load', htmxDrupalBehaviors);
      // Attach Drupal behaviors after an HTMX 'out of band' content swap.
      window.addEventListener('htmx:oobAfterSwap', htmxDrupalBehaviors);
    },
  };
})(Drupal, drupalSettings, once, htmx, loadjs);
