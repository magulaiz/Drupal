((Drupal, once) => {
  Drupal.behaviors.navigationFormActions = {
    attach: (context) => {
      // Should be some common attribute.
      const forms = context.querySelectorAll(
        '.node-form, .taxonomy-term-form, .media-form',
      );
      once('form-submit-action', forms).forEach((form) => {
        let actionButtons;
        const newButtons = [];
        const topBar = document.querySelector('.top-bar__content');
        const action = form.querySelector('.form-actions');

        if (topBar && action) {
          actionButtons = document.getElementById('edit-actions').children;
          for (let i = 0; i < actionButtons.length; i++) {
            if (actionButtons[i].type === 'submit') {
              const attributesArray = [...actionButtons[i].attributes];

              // Filter attributes.
              const filterAttribute = (attribute) => {
                return (
                  attribute.includes('data-drupal-selector') ||
                  attribute.includes('type') ||
                  attribute.includes('name') ||
                  attribute.includes('value') ||
                  attribute.includes('id')
                );
              };
              newButtons[i] = document.createElement('button');
              attributesArray.map((attribute) => {
                if (filterAttribute(attribute.name)) {
                  newButtons[i].setAttribute(
                    attribute.name,
                    actionButtons[i].getAttribute(attribute.name),
                  );
                }
                return newButtons;
              });
              newButtons[i].className =
                'toolbar-button toolbar-button--primary';
              newButtons[i].textContent = newButtons[i].getAttribute('value');
            } else {
              newButtons[i] = actionButtons[i].cloneNode(true);
            }
          }
          newButtons.reverse().forEach(function (item) {
            topBar.insertBefore(item, topBar.firstChild);
          });

          // Set the form attribute for the buttons
          // to define which form these buttons belong to.
          topBar.querySelectorAll('.toolbar-button').forEach((button) => {
            button.setAttribute('form', form.getAttribute('id'));
          });
        }
      });
    },
  };
})(Drupal, once);
