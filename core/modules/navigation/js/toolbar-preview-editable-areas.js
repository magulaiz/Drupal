/**
 *
 * Preview editable areas button in the toolbar.
 *
 * Toggle contextual links in the entire page.
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
        const editableAreaBtn = once(
          'preview-editable-areas',
          '.js-navigation-contextual-toggle',
          context,
        );
        const contextualLinks = document.querySelectorAll('.contextual-region');
        if (!contextualLinks.length || !editableAreaBtn.length) {
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
        editableAreaBtn.forEach((btn) => {
          btn.removeAttribute('hidden');
          btn.classList.remove('hidden');
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
