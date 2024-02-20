((Drupal, once, { focusable }) => {
  Drupal.behaviors.submitButtonProgress = {
    attach(context) {
      once('submit-buttons', '[data-progress-message]', context).forEach(
        (button) => {
          const parentForm = button.form;
          parentForm.addEventListener('submit', (e) => {
            // Bail if the clicked button does not have a progress message.
            if (e.submitter !== button) {
              return;
            }

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
