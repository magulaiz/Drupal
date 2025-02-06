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

        const editableAreaBtnRegion = editableAreaBtn.parentElement;
        const contextualLinks = document.querySelectorAll('.contextual-region');
        // If no contextual links are present and only the "Editable Areas" button appears in the top bar tools section,
        // remove the button and clear the tools section to ensure the toolbar keep hidden.
        if (contextualLinks.length === 0) {
          // Determine the toolbar region selector based on the button's parent class.
          let toolbarRegionSelector = 'top-bar__tools';
          if (editableAreaBtnRegion.classList.contains('top-bar__actions')) {
            toolbarRegionSelector = 'top-bar__actions';
          } else if (
            editableAreaBtnRegion.classList.contains('top-bar__context')
          ) {
            toolbarRegionSelector = 'top-bar__context';
          }
          const toolbarSection = document.querySelector(
            `.${toolbarRegionSelector}`,
          );
          // If the toolbar section contains only the "Editable Areas" button, remove the button and clear the section.
          if (
            toolbarSection.children.length === 1 &&
            toolbarSection.children[0].classList.contains(
              'navigation-contextual-link',
            )
          ) {
            // Remove the button and clear the section to ensure the toolbar keep hidden.
            // Check the css rule at top-bar.pcss.css:36
            toolbarSection.innerHTML = '';
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
