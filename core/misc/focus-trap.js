((Drupal) => {
  /**
   * Array of elements in which focus will be available.
   */
  let focusTrapElements = [];

  Drupal.focusTrap = {};

  /**
   * Add a focus trap
   * @param {Array} elements
   */
  Drupal.focusTrap.add = (elements) => {
    focusTrapElements = elements;
    document.addEventListener('keydown', createFocusTrap);
  };

  /**
   * Creates the focus trap.
   * @param {event} e - keydown event object
   */
  createFocusTrap = (e) => {
    if (e.key === 'Tab') {
      const tabbableElements = [];

      focusTrapElements.forEach((element) => {
        tabbableElements.push(
          ...tabbable.tabbable(element, { includeContainer: true }),
        );
      });

      const firstTabbableEl = tabbableElements[0];
      const lastTabbableEl = tabbableElements[tabbableElements.length - 1];

      if (e.shiftKey) {
        if (document.activeElement === firstTabbableEl) {
          lastTabbableEl.focus();
          e.preventDefault();
        }
      } else if (document.activeElement === lastTabbableEl) {
        firstTabbableEl.focus();
        e.preventDefault();
      }
    }
  };

  Drupal.focusTrap.remove = () => {
    document.removeEventListener('keydown', createFocusTrap);
  };
})(Drupal);
