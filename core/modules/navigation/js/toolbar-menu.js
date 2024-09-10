/**
 *
 * Toolbar menu code.
 *
 * Toggle own state
 * Listens toolbar-menu-set-toggle to change state.
 *
 * @type {Drupal~behavior}
 *
 * @prop {Drupal~behaviorAttach} attach
 */

(
  (Drupal, once) => {
    /**
     * Constant for the "toolbar-menu-set-toggle" event name.
     *
     * @type {string}
     */
    const TOOLBAR_MENU_SET_TOGGLE = 'toolbar-menu-set-toggle';

    /**
     * Initializes menu buttons.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *  Toggles aria-expanded attribute.
     *  Changes buttons inner text.
     *  Listens event when should be expanded.
     */
    Drupal.behaviors.navigationProcessToolbarMenuTriggers = {
      attach: (context) => {
        once(
          'toolbar-menu-trigger',
          '[data-toolbar-menu-trigger]',
          context,
        ).forEach((button) => {
          const menu = button.nextElementSibling;

          /**
           * Element containing the button text.
           *
           * @type {HTMLElement}
           */
          const text = button.querySelector('.toolbar-menu__link-action');

          /**
           * Toggles the button's aria-expanded attribute and updates its text.
           * This is only one function which change state of button.
           *
           * @param {boolean} state The button state it should be expanded or collapsed.
           */
          const toggleButtonState = (state) => {
            button.setAttribute('aria-expanded', state);
            if (text) {
              text.textContent = state
                ? Drupal.t('Collapse')
                : Drupal.t('Extend');
            }
            if (state) {
              menu.removeAttribute('inert');
            } else {
              menu.setAttribute('inert', true);
            }
          };

          button.addEventListener('click', (e) => {
            const level = e.currentTarget.dataset.toolbarMenuTrigger;
            const state =
              e.currentTarget.getAttribute('aria-expanded') === 'false';
            toggleButtonState(state);
            button.dispatchEvent(
              new CustomEvent('toolbar-menu-toggled', {
                bubbles: true,
                detail: {
                  state,
                  level,
                },
              }),
            );
          });

          // State of submenu button can be changed by CustomEvent.
          button.addEventListener(TOOLBAR_MENU_SET_TOGGLE, (e) => {
            const newState = e.detail.state;
            toggleButtonState(newState);
          });
        });
      },
    };

    /**
     * Initializes menu links.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *
     * When current url it adds classes and dispatch event to popover.
     */
    Drupal.behaviors.navigationProcessToolbarMenuLinks = {
      attach: (context) => {
        // Check if a menu link is in the current URL and handle its behavior.
        const handleMenuLink = (link) => {
          if (document.URL === link.href) {
            link.classList.add('current', 'is-active');
            link.dispatchEvent(
              new CustomEvent('toolbar-active-url', { bubbles: true }),
            );

            // Open all parent menus.
            const menu = link.closest('.toolbar-menu');
            if (menu) {
              menu.previousElementSibling.dispatchEvent(
                new CustomEvent(TOOLBAR_MENU_SET_TOGGLE, {
                  detail: { state: true },
                }),
              );
            }
          }
        };

        // Select toolbar menu links and buttons and iterate over them.
        const toolbarLinks = once(
          'toolbar-menu-link',
          context.querySelectorAll('a.toolbar-menu__link, a.toolbar-button'),
        );
        toolbarLinks.forEach((link) => {
          handleMenuLink(link);
        });

        // MutationObserver to observe changes in toolbar menus for mutations.
        const menuObserver = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (
              mutation.type === 'attributes' &&
              mutation.attributeName === 'class'
            ) {
              // Check if the target element has the class 'toolbar-popover--expanded'.
              if (
                mutation.target.classList.contains('toolbar-popover--expanded')
              ) {
                // Find the active submenu.
                const activeSubmenu =
                  mutation.target.querySelector('.is-active');
                if (activeSubmenu) {
                  // Find the active button and set its 'aria-expanded' attribute to 'true'.
                  const activeButton = activeSubmenu
                    .closest('li.toolbar-menu__item--level-1')
                    .querySelector('button.toolbar-button');
                  if (activeButton) {
                    activeButton.setAttribute('aria-expanded', 'true');
                  }
                }
              }
            }
          });
        });

        // Start observing the admin toolbar menus for mutations.
        menuObserver.observe(document.getElementById('admin-toolbar'), {
          attributes: true,
          attributeFilter: ['class'],
          subtree: true,
        });
      },
    };
  }
)(Drupal, once);
