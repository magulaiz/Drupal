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
     * Create navigation toggle contextual links button.
     *
     * @return {HTMLButtonElement}
     *   The button element.
     */
    const navigationToggleContextualLinksBtn = () => {
      const button = document.createElement('button');
      // Add classes
      button.classList.add(
        'toolbar-button',
        'navigation-contextual-link',
        'toolbar-button--icon--close-preview',
      );

      // Set attributes
      button.setAttribute('aria-label', Drupal.t('Editable areas'));
      button.setAttribute('aria-pressed', 'false');

      // Set button text
      button.textContent = Drupal.t('Editable areas');
      return button;
    };

    /**
     * Toggle contextual menu links.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *  Attach event into the navigation contextual link.
     */
    Drupal.behaviors.navigationToggleContextualLinks = {
      attach: () => {
        const contextualLinks = document.querySelectorAll('.contextual-region');
        const buttonAlreadyAdded = document.querySelector(
          '.navigation-contextual-link',
        );
        if (contextualLinks.length === 0 || buttonAlreadyAdded) {
          return;
        }

        const sections = document.querySelector('.top-bar .top-bar__content');
        const toolsSection = sections.querySelector('.top-bar__tools');
        if (!toolsSection) return;

        // Add the Editable areas button to the toolbar.
        toolsSection?.appendChild(navigationToggleContextualLinksBtn());
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

        once('preview-editable-areas', '.navigation-contextual-link').forEach(
          (btn) => {
            // Set initial state
            toggleButtonState(
              localStorage.getItem('Drupal.contextualToolbar.isViewing') ===
                null,
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
          },
        );
      },
    };
  }
)(Drupal, once);
