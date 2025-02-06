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
     * Toggle contextual menu links.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *  Attach event into the navigation contextual link.
     */
    Drupal.behaviors.navigationToggleContextualLinks = {
      attach: (context) => {
        const editableAreaBtn = document.querySelector(
          'button.navigation-contextual-link',
        );
        if (!editableAreaBtn) {
          return;
        }

        const contextualLinks = document.querySelectorAll('.contextual-region');
        // If no contextual links are present and only the "Editable Areas" button appears in the top bar tools section,
        // remove the button and the top bar.
        if (contextualLinks.length === 0) {
          editableAreaBtn.remove();
          const topBar = document.querySelector('.top-bar');
          const sections = topBar?.querySelector('.top-bar__content');

          if (!sections) return;

          const hasContent = Array.from(sections.children).some(
            (child) => child.children.length > 0,
          );
          if (!hasContent) {
            topBar.remove();
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
