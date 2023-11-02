// cspell:ignore popovertarget
/**
 * @file
 * Attaches behaviors for adding tooltip functionality.
 */
// cspell:ignore UIDOM Wolfson

((
  Drupal,
  displace,
  { computePosition, flip, shift, offset, autoUpdate, arrow },
) => {
  Drupal.tooltip = {
    defaultConfig: {
      placement: 'top-end',
      offset: 24,
      shiftPadding: 5,
    },
  };
  Drupal.behaviors.tooltip = {
    attach(context) {
      const { defaultConfig } = Drupal.tooltip;

      once(
        'drupal-tooltip-trigger',
        '[data-drupal-tooltip-toggle-button]',
        context,
      ).forEach((tooltipTrigger) => {
        const tip = Drupal.theme.tooltip(
          tooltipTrigger.getAttribute('popovertarget'),
          tooltipTrigger.dataset.drupalTooltipToggleButton,
        );

        const config = { ...defaultConfig };

        // [data-drupal-tooltip-placement] from https://floating-ui.com/docs/computePosition#placement-1
        if (tooltipTrigger.dataset.drupalTooltipPlacement) {
          config.placement = tooltipTrigger.dataset.drupalTooltipPlacement;
        }

        // Create the arrow that points to the tip's disclosure button.
        const tipArrow = Drupal.theme.tooltipArrow(tip);

        tooltipTrigger.after(tip);

        // Position the toggletip.
        autoUpdate(tooltipTrigger, tip, () => {
          displace();
          computePosition(tooltipTrigger, tip, {
            placement: config.placement,
            middleware: [
              offset(config.offset),
              // Account for Drupal's top offset so the toggletip is not hidden
              // under the toolbar.
              flip({
                padding: {
                  top: displace.offsets.top
                    ? displace.offsets.top + config.offset
                    : 0,
                },
                crossAxis: false,
              }),
              shift({ padding: config.shiftPadding }),
              arrow({ element: tipArrow }),
            ],
            // eslint-disable-next-line max-nested-callbacks
          }).then(({ x, y, placement, middlewareData }) => {
            // Position the tip.
            const { left, right } = displace.offsets;

            // If the default X position is less than the left offset, the x position
            // should begin at the left offset.
            let offsetX = x < left ? left : x;

            // If the default X position is less than 0, then just add the
            // offset.
            if (x < 0) {
              offsetX = left + x;
            }
            const { marginLeft, marginRight } = getComputedStyle(tip);
            const marginOffset =
              parseInt(marginLeft.replace(/\D/g, ''), 10) +
              parseInt(marginRight.replace(/\D/g, ''), 10);
            Object.assign(tip.style, {
              left: `${offsetX}px`,
              top: `${y}px`,
              position: 'absolute',
              width:
                left || right
                  ? `${
                      document.body.offsetWidth -
                      // Subtract right because right offset does not change
                      // the body offset width.
                      right -
                      config.offset -
                      config.shiftPadding -
                      marginOffset
                    }px`
                  : 'auto',
            });

            // Use middleware to dynamically position the arrow.
            const { x: arrowX, y: arrowY } = middlewareData.arrow;

            // Placement will the opposite of the tip's primary axis.
            const staticSide = {
              top: 'bottom',
              right: 'left',
              bottom: 'top',
              left: 'right',
            }[placement.split('-')[0]];

            let offsetArrowX = arrowX - marginOffset / 2;

            if (offsetX !== x) {
              const addToOffset = x > 0 && arrowX > left ? x : 0;
              offsetArrowX = Math.abs(arrowX - left) + addToOffset;
            }

            Object.assign(tipArrow.style, {
              left: arrowX != null ? `${offsetArrowX}px` : '',
              top: arrowY != null ? `${arrowY}px` : '',
              right: '',
              bottom: '',
              [staticSide]: '-4px',
            });
          });
        });
      });
    },
  };

  /**
   * Theme function for a tip.
   *
   * @param {string} tipId
   *   The tipId.
   * @param {string} content
   *   The tooltip text.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.tooltip = (tipId, content) => {
    const tip = document.createElement('div');
    tip.classList.add('tooltip');
    tip.setAttribute('tabindex', '0');
    tip.setAttribute('role', 'status');
    tip.setAttribute('data-drupal-tooltip', true);
    tip.id = tipId;
    tip.setAttribute('popover', '');
    tip.innerHTML = content;
    return tip;
  };

  /**
   * Theme function for a tipArrow.
   *
   * @param {HTMLElement} tip
   *   The tip.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.tooltipArrow = (tip) => {
    const tipArrow = document.createElement('div');
    tipArrow.classList.add('tooltip__arrow');
    tip.append(tipArrow);
    return tipArrow;
  };
})(Drupal, Drupal.displace, window.FloatingUIDOM);
