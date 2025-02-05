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
          const text = button.querySelector('[data-toolbar-action]');

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
        once(
          'toolbar-menu-link',
          'a.toolbar-menu__link, a.toolbar-button',
          context,
        ).forEach((link) => {
          // What we do if menu link is in current url.
          if (document.URL === link.href) {
            link.classList.add('current', 'is-active');

            link.dispatchEvent(
              new CustomEvent('toolbar-active-url', {
                bubbles: true,
              }),
            );

            // We also want to open all parent menus.
            const menu = link.closest('.toolbar-menu');
            if (menu) {
              menu.previousElementSibling.dispatchEvent(
                new CustomEvent(TOOLBAR_MENU_SET_TOGGLE, {
                  detail: {
                    state: true,
                  },
                }),
              );
            }
          }
        });
      },
    };

    /**
     * Toggle contextual menu links.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *  Attach event into the navigation contextual link.
     */
    Drupal.behaviors.toggleContextualLinks = {
      attach: (context) => {
        const editableAreaBtn = document.querySelector(
          'button.navigation-contextual-link',
        );
        const editableAreas = document.querySelectorAll('.contextual-region');
        // If no contextual links are present and only the "Editable Areas" button appears in the top bar tools section,
        // remove the button and clear the tools section to ensure the toolbar keep hidden.
        if (editableAreas.length === 0 && editableAreaBtn) {
          const toolbarToolsSection = document.querySelector('.top-bar__tools');
          if (
            toolbarToolsSection.children.length === 1 &&
            toolbarToolsSection.children[0].classList.contains(
              'navigation-contextual-link',
            )
          ) {
            toolbarToolsSection.innerHTML = '';
          }
          return;
        }

        const showText = Drupal.t('Editable areas');
        const hideText = Drupal.t('Hide editable areas');

        const toggleButtonState = (isEditing, btn) => {
          btn.classList.toggle('toolbar-button--icon--preview', isEditing);
          btn.classList.toggle(
            'toolbar-button--icon--close-preview',
            !isEditing,
          );
          btn.textContent = !isEditing ? hideText : showText;
        };

        once(
          'preview-editable-areas',
          '.navigation-contextual-link',
          context,
        ).forEach((btn) => {
          // Set initial state
          toggleButtonState(
            localStorage.getItem('Drupal.contextualToolbar.isViewing') === null,
            btn,
          );
          // Listen to click event.
          btn.addEventListener('click', (e) => {
            toggleButtonState(
              !Drupal.contextualToolbar.model.get('isViewing'),
              btn,
            );
            Drupal.contextualToolbar.model.set(
              'isViewing',
              !Drupal.contextualToolbar.model.get('isViewing'),
            );
            e.preventDefault();
          });
        });
      },
    };
  }
)(Drupal, once);
