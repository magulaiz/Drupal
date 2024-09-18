((Drupal, once) => {
  Drupal.behaviors.navigationFormActions = {
    attach: (context) => {
      // Should be some common attribute.
      const forms = context.querySelectorAll('.node-form, .taxonomy-term-form, .media-form');
      once('form-submit-action', forms).forEach((form) => {
        const topBar = document.querySelector('.top-bar__content');
        const action = form.querySelector('.form-actions');

        if (topBar && action) {
          // Place the form action buttons on top bar.
          while (action.lastElementChild) {
            topBar.insertBefore(action.lastElementChild, topBar.firstChild);
          }

          // Set the form attribute for the buttons
          // to define which form these buttons belong to.
          topBar.querySelectorAll('.form-submit').forEach((button) => {
            button.setAttribute('form', form.getAttribute('id'));
          });
        }
      });
    },
  };
})(Drupal, once);
