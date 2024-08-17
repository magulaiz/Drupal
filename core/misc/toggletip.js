/**
 * @file
 * Attaches behaviors for adding toggle tip functionality.
 */
// cspell:ignore UIDOM Wolfson

((Drupal, { computePosition }) => {
  // Keeps track of generated ids to ensure no duplicates are created.
  const toggletipIds = new Set();
  Drupal.toggletip = {
    defaultConfig: {
      atDescription: '',
      offset: 24,
    },
  };

  /**
   * Get the line height of an element.
   *
   * @param {element} node
   *   The element to get the line height of.
   * @return {Number}
   *  The pixel count of the line height.
   *
   * This is code based on the project https://github.com/twolfson/line-height,
   * copyright (c) 2013 Todd Wolfson.  These classes are licensed under the MIT
   * license. For additional details, see
   * https://raw.githubusercontent.com/twolfson/line-height/master/LICENSE-MIT.
   */
  const getLineHeight = (node) => {
    // Grab the line-height via style
    let lineHeightString = getComputedStyle(node)['line-height'];
    let lineHeight = parseFloat(lineHeightString, 10);

    // If the lineHeight did not contain a unit (i.e. it was numeric), convert it to ems (e.g. '2.3' === '2.3em')
    if (lineHeightString === `${lineHeight}`) {
      // Save the old lineHeight style and update the em unit to the element
      const _lineHeightStyle = node.style.lineHeight;
      node.style.lineHeight = `${lineHeightString}em`;

      // Calculate the em based height.
      lineHeightString = getComputedStyle(node)['line-height'];
      lineHeight = parseFloat(lineHeightString, 10);

      // Revert the lineHeight style
      if (_lineHeightStyle) {
        node.style.lineHeight = _lineHeightStyle;
      } else {
        delete node.style.lineHeight;
      }
    }

    lineHeight = Math.round(lineHeight);

    // If the line-height is "normal", calculate by font-size.
    if (lineHeightString === 'normal') {
      // Create a temporary node
      const { nodeName } = node;
      const _node = document.createElement(nodeName);
      _node.innerHTML = '&nbsp;';
      _node.style.fontSize = getComputedStyle(node)['font-size'];

      // Remove default padding/border which can affect offset height.
      _node.style.padding = '0px';
      _node.style.border = '0px';

      // Append it to the body.
      document.body.appendChild(_node);

      // Assume the line height of the element is the height.
      lineHeight = _node.offsetHeight;

      // Remove the temporary node from the DOM.
      document.body.removeChild(_node);
    }

    // Return the calculated height.
    return lineHeight;
  };

  const placeButton = (button, tipElement) => {
    computePosition(tipElement, button, {
      placement: button.dataset.drupalToggletipPositioned,
    }).then(({ x, y }) => {
      Object.assign(button.style, {
        left: `${x}px`,
        top: `${y}px`,
      });
    });
  };

  /**
   * Constructs a toggletip element.
   *
   * @see https://codepen.io/aardrian/pen/NWpoVQd
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   * Attaches the autocomplete behaviors.
   */
  Drupal.behaviors.toggletip = {
    attach(context) {
      const generateId = () => {
        const id = Math.random().toString(36).substring(2, 15);
        if (toggletipIds.has(id)) {
          return generateId();
        }
        toggletipIds.add(id);
        return id;
      };

      const { defaultConfig } = Drupal.toggletip;

      once('drupal-tip', '[data-drupal-toggletip]', context).forEach(
        (tipElement) => {
          const toggletipConfig = JSON.parse(
            tipElement.getAttribute('data-drupal-toggletip'),
          );
          if (!toggletipConfig.content) {
            return;
          }

          const config = { ...defaultConfig, ...toggletipConfig };

          // Create unique ids for each part of the toggletip.
          const id = generateId();
          const toggleId = `${id}-toggle`;
          const descriptionId = `${id}-description`;
          const tipId = `${id}-tip`;

          if (!config.atDescription.length) {
            console.warn(
              `The atDescription property of toggle button ${toggleId} is empty, and is using a default value. To be sufficiently accessible, this property should describe the information this button reveals.`,
            );
            config.atDescription = Drupal.t('More info about this');
          }

          // Create the toggle button.
          const button = Drupal.theme.toggletipButton(
            descriptionId,
            tipId,
            toggleId,
            config,
          );

          const isFormElement = tipElement.hasAttribute(
            'data-drupal-toggletip-form-element',
          );

          // When a toggletip option is directly added to a details element, the
          // button is appended to its summary. To add a toggletip inside a
          // details element, add it to a child render array.
          if (
            tipElement.tagName === 'DETAILS' &&
            tipElement.querySelector('summary')
          ) {
            const summary = tipElement.querySelector('summary');
            summary.append(button);
          } else if (isFormElement) {
            // If a toggletip is attached to a form element, the button should
            // append to that element's label.
            const label = tipElement
              .closest('.js-form-item')
              .querySelector('label');
            if (label) {
              label.parentNode.insertBefore(button, label.nextSibling);
            }
          } else {
            tipElement.append(button);
          }

          // Determine the line height so the toggle button can be vertically
          // centered.
          const elementLineHeight = getLineHeight(tipElement);

          if (
            !isFormElement &&
            elementLineHeight > button.offsetHeight &&
            !config.place
          ) {
            // If the element receiving the toggle button has a larger line
            // height than the toggle button, vertically position the button
            // based on the element's line height. This ensures vertical
            // centering even if the text wraps.
            button.style.position = 'relative';

            const halfTallerDivider =
              elementLineHeight / 2 > button.clientHeight ? 1 : 2;
            const pixel = -Math.abs(
              elementLineHeight / 2 -
                button.clientHeight / halfTallerDivider -
                2,
            );

            button.style.top = `${pixel}px`;
          }

          // If the button placement is explicitly set, parse the positioning
          // string into styles that place the toggle button in the expected
          // position.
          if (config.place) {
            tipElement.setAttribute(
              'data-drupal-toggletip-position',
              config.place,
            );
            button.setAttribute(
              'data-drupal-toggletip-positioned',
              config.place,
            );
            placeButton(button, tipElement, config);
          }

          Drupal.behaviors.tip.attach(tipElement);
        },
      );
    },
  };
  /**
   * Theme function for a button.
   *
   * @param {string} descriptionId
   *   The descriptionId.
   * @param {string} tipId
   *   The tipId.
   * @param {string} toggleId
   *   The toggleId.
   * @param {object} config
   *   The config.
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.toggletipButton = (descriptionId, tipId, toggleId, config) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.setAttribute('data-drupal-tip-toggle-button', config.content);
    button.setAttribute('aria-expanded', false);
    button.setAttribute('aria-labelledby', descriptionId);
    button.setAttribute('aria-controls', tipId);
    button.classList.add('toggletip');
    button.innerHTML = `i`;
    button.id = toggleId;
    button.setAttribute('popovertarget', tipId);
    return button;
  };
})(Drupal, window.FloatingUIDOM);
