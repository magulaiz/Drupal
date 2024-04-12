/**
 *
 * Toolbar popover.
 *
 * @type {Drupal~behavior}
 *
 * @prop {Drupal~behaviorAttach} attach
 */

const POPOVER_OPEN_DELAY = 150;
const POPOVER_CLOSE_DELAY = 400;

((Drupal, once) => {
  Drupal.behaviors.navigationProcessPopovers = {
    /**
     * Attaches the behavior to the context element.
     *
     * @param {HTMLElement} context The context element to attach the behavior to.
     */
    attach: (context) => {
      once(
        'toolbar-popover',
        context.querySelectorAll('[data-toolbar-popover]'),
      ).forEach((popover) => {
        // This is trigger of popover. Currently only first level button.
        const button = popover.querySelector('[data-toolbar-popover-control]');
        // This is tooltip content. Currently child menus only.
        const tooltip = popover.querySelector('[data-toolbar-popover-wrapper]');

        if (!button || !tooltip) return;

        const handleMouseMove = (event) => {
          button.style.setProperty(
            '--safe-triangle-cursor-x',
            `${event.clientX}px`,
          );
          button.style.setProperty(
            '--safe-triangle-cursor-y',
            `${event.clientY}px`,
          );
        };

        /**
         * We need to change state of trigger and popover.
         *
         * @param {boolean} state The popover state.
         *
         * @param {boolean} initialLoad Happens on page loads.
         */
        const toggleState = (state, initialLoad = false) => {
          /* eslint-disable-next-line no-unused-expressions */
          state && !initialLoad
            ? popover.classList.add('toolbar-popover--expanded')
            : popover.classList.remove('toolbar-popover--expanded');
          button.setAttribute('aria-expanded', state && !initialLoad);

          const text = button.querySelector('[data-toolbar-action]');
          if (text) {
            text.textContent = state
              ? Drupal.t('Collapse')
              : Drupal.t('Extend');
          }
        };

        const isPopoverHoverOrFocus = () =>
          popover.contains(document.activeElement) || popover.matches(':hover');

        const delayedClose = () => {
          setTimeout(() => {
            if (isPopoverHoverOrFocus()) return;
            // eslint-disable-next-line no-use-before-define
            close();
          }, POPOVER_CLOSE_DELAY);
        };

        const open = () => {
          ['mouseleave', 'focusout'].forEach((e) => {
            button.addEventListener(e, delayedClose, false);
            tooltip.addEventListener(e, delayedClose, false);
          });
        };

        const close = () => {
          toggleState(false);
          ['mouseleave', 'focusout'].forEach((e) => {
            button.removeEventListener(e, delayedClose);
            tooltip.removeEventListener(e, delayedClose);
          });
        };

        button.addEventListener('mousemove', handleMouseMove);

        ['mouseover', 'keyup', 'keydown'].forEach((e) => {
          button.addEventListener(e, () => {
            if (e === 'keydown' && e.shiftKey && e.keyCode === 9) {
              close();
              return;
            }

            if ((e === 'keyup' && e.keyCode !== 13) || e === 'keydown') {
              return;
            }

            // This is not needed because no hover on mobile.
            // @todo test is after.

            if (
              window.matchMedia('(max-width: 1023px)').matches &&
              e === 'mouseover'
            ) {
              return;
            }

            const delay = e === 'mouseover' ? POPOVER_OPEN_DELAY : 0;
            setTimeout(() => {
              // If it is accident hover ignore it.
              // If in this timeout popover already opened by click.
              if (
                e === 'mouseover' &&
                (!button.matches(':hover') ||
                  !button.getAttribute('aria-expanded') === 'false')
              ) {
                return;
              }

              toggleState(true);

              // Dispatch event to sidebar.js
              popover.dispatchEvent(
                new CustomEvent('toolbar-popover-toggled', {
                  bubbles: true,
                  detail: {
                    state: true,
                  },
                }),
              );
              open();
            }, delay);
          });
        });

        button.addEventListener('click', (e) => {
          const state =
            e.currentTarget.getAttribute('aria-expanded') === 'false';
          toggleState(state);

          // Dispatch event to sidebar.js
          popover.dispatchEvent(
            new CustomEvent('toolbar-popover-toggled', {
              bubbles: true,
              detail: {
                state,
              },
            }),
          );
        });

        // Listens events from sidebar.js.
        popover.addEventListener('toolbar-popover-close', () => {
          close();
        });

        // Listens events from toolbar-menu.js
        popover.addEventListener('toolbar-active-url', () => {
          toggleState(true, true);
        });
      });
    },
  };
})(Drupal, once);
