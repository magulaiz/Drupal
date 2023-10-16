// cspell:ignore popovertarget
/**
 * @file
 * Attaches behaviors for adding toggle tip functionality.
 */
// cspell:ignore UIDOM Wolfson

((Drupal) => {
  // Keeps track of generated ids to ensure no duplicates are created.
  const toggletipIds = new Set();
  Drupal.toggletip = {
    defaultConfig: {
      atDescription: '',
      offset: 24,
      positionOffsetPrimary: 0,
      positionOffsetSecondary: 0,
      shiftPadding: 5,
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

  const placeButton = (button, tipElement, config) => {
    const { offsetHeight, offsetWidth } = button;
    tipElement.style.position = 'relative';
    button.style.position = 'absolute';
    const places = config.place.split('-');

    const mainPosition = {
      left: {
        style: 'inset-inline-start',
        offset: 0 - offsetWidth * 1.5 - config.offset / 4,
      },
      right: {
        style: 'inset-inline-end',
        offset: 0 - offsetWidth * 1.5 - config.offset / 4,
      },
      top: { style: 'inset-block-start', offset: 0 - offsetHeight },
      bottom: { style: 'inset-block-end', offset: 0 - offsetHeight },
    }[places[0]];

    button.style[mainPosition.style] = `${mainPosition.offset}px`;

    if (places[1]) {
      const secondaryPosition = {
        start: ['left', 'right'].includes(places[0])
          ? { style: 'inset-block-start', offset: 0 }
          : {
              style: 'inset-inline-start',
              offset: 0 - offsetWidth / 2,
            },
        end: ['left', 'right'].includes(places[0])
          ? { style: 'inset-block-end', offset: 0 }
          : { style: 'inset-inline-end', offset: 0 - offsetWidth / 2 },
      }[places[1]];
      button.style[secondaryPosition.style] = `${secondaryPosition.offset}px`;
    } else {
      const properties = ['left', 'right'].includes(places[0])
        ? { start: 'top', end: 'bottom' }
        : { start: 'left', end: 'right' };

      button.style[`margin-${properties.start}`] = 'auto';
      button.style[`margin-${properties.end}`] = 'auto';
      button.style[properties.start] = '0';
      button.style[properties.end] = '0';
    }
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

          tipElement.classList.add('toggletip');
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

          // When a toggletip option is directly added to a details element, the
          // button is appended to its summary. To add a toggletip inside a
          // details element, add it to a child render array.
          if (
            tipElement.tagName === 'DETAILS' &&
            tipElement.querySelector('summary')
          ) {
            const summary = tipElement.querySelector('summary');
            summary.append(button);
          } else if (
            tipElement.hasAttribute('data-drupal-toggletip-form-element')
          ) {
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

          Drupal.behaviors.tooltip.attach(tipElement);

          // Determine the line height so the toggle button can be vertically
          // centered.
          const elementLineHeight = getLineHeight(tipElement);

          if (elementLineHeight > button.offsetHeight && !config.place) {
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
            placeButton(button, tipElement, config);
          }
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
    button.setAttribute('data-drupal-tooltip-toggle-button', config.content);
    button.setAttribute('aria-expanded', false);
    button.setAttribute('aria-labelledby', descriptionId);
    button.setAttribute('aria-controls', tipId);
    button.classList.add('toggletip__toggle');
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"><path fill="#55565B" d="M7.194 2.073a1.729 1.729 0 0 0-.131.677c0 .24.042.463.127.67.085.205.214.394.384.566.171.17.361.3.568.386.209.085.436.128.68.128.24 0 .461-.044.666-.13a1.74 1.74 0 0 0 .562-.389c.172-.172.3-.362.385-.566a1.71 1.71 0 0 0 .127-.665c0-.243-.042-.47-.128-.675a1.7 1.7 0 0 0-.384-.564 1.79 1.79 0 0 0-.564-.382A1.72 1.72 0 0 0 8.822 1a1.73 1.73 0 0 0-1.236.51 1.696 1.696 0 0 0-.392.563ZM7.689 12.582l.001-.006 2.091-7.1.004-.025a.078.078 0 0 0-.08-.076l-4.935.159a.076.076 0 0 0-.074.058l-.254.95-.005.027c0 .043.035.079.077.079h.966c.475 0 .666.29.613.738-.032.268-.122.555-.192.816l-1.106 3.441c-.22.722-.447 1.561-.17 2.3.442 1.168 1.98 1.231 2.954.844.35-.14.691-.35 1.025-.627l.005-.004c.333-.277.662-.627.986-1.05.322-.419.64-.91.956-1.473a.08.08 0 0 0-.02-.106l-.684-.523a.075.075 0 0 0-.105.028c-.202.347-.396.658-.58.928a8.133 8.133 0 0 1-.536.708 2.95 2.95 0 0 1-.45.441c-.122.09-.223.137-.303.137-.365 0-.246-.436-.184-.664Z"/></svg> <span id=${descriptionId} class="visually-hidden">${config.atDescription}</span>`;
    button.id = toggleId;
    button.setAttribute('popovertarget', tipId);
    return button;
  };
})(Drupal);
