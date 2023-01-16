((Drupal, once, { focusable }) => {
  Drupal.behaviors.submitButtonProgress = {
    attach(context) {
      once('submit-buttons', '[data-progress-message]', context).forEach(
        (button) => {
          const parentForm = button.form;
          parentForm.addEventListener('submit', (e) => {
            button.disabled = true;
            const throbber = Drupal.theme(
              'ajaxProgressThrobber',
              button.getAttribute('data-progress-message'),
            );
            button.parentNode.innerHTML += throbber;
          });
        },
      );
    },
  };
})(Drupal, once, window.tabbable);
