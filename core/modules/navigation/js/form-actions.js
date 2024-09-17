((Drupal, $, once) => {
  Drupal.behaviors.navigationFormActions = {
    attach: (context) => {
      // Should be some common attribute.
      const form = $(context).find('.node-form, .taxonomy-term-form, .media-form');
      $(once('form-submit-action', form, context)).each(() => {
        const topBar = $('.top-bar__content');
        const action = form.find('.form-actions');

        if (topBar && action) {
          // Prepend the form action buttons on top bar
          topBar.prepend(action.contents().unwrap());

          // Set the form attribute for the buttons
          // to define which form these buttons belong to.
          topBar.find('.form-submit').attr('form', form.attr('id'));
        }
      });
    },
  };
})(Drupal, jQuery, once);
