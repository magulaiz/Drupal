((Drupal, once, { focusable }) => {
  console.log('hi buddy');
  Drupal.behaviors.submitButtonProgress = {
    attach(context) {
      once('submit-buttons', '[data-progress-message]', context).forEach(
        (button) => {
          const parentForm = button.form;
          button.addEventListener('click', (e) => {
            return;
            // @todo making any changes within the form here seems to prevent
            // the form from submitting.
            // If I open a modal dialog instead of modifying form contents, the
            // form will submit.
            // eslint-disable-next-line no-unreachable
            button.disabled = true;
            const throbber = Drupal.theme(
              'ajaxProgressThrobber',
              button.getAttribute('data-progress-message'),
            );
            button.parentNode.innerHTML += throbber;
            if (parentForm) {
              focusable(parentForm).forEach((focusableElement) => {
                // focusableElement.disabled = true;
              });
            }
          });
        },
      );
    },
  };
})(Drupal, once, window.tabbable);
