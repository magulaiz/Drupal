((Drupal, once) => {
  Drupal.behaviors.navigationFormActions = {
    attach: (context) => {
      // Should be some common attribute.
      const forms = context.querySelectorAll(
        '.node-form, .taxonomy-term-form, .media-form',
      );
      once('form-submit-action', forms).forEach((form) => {
        let actionBtns;
        const newBtns = [];
        const topBar = document.querySelector('.top-bar__content');
        const action = form.querySelector('.form-actions');

        if (topBar && action) {
          actionBtns = document.getElementById('edit-actions').children;
          for (let i = 0; i < actionBtns.length; i++) {
            if (actionBtns[i].type === 'submit') {
              const attributesArray = [...actionBtns[i].attributes];

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
              newBtns[i] = document.createElement('button');
              attributesArray.map((attribute) => {
                if (filterAttribute(attribute.name)) {
                  newBtns[i].setAttribute(
                    attribute.name,
                    actionBtns[i].getAttribute(attribute.name),
                  );
                }
                return newBtns;
              });
              newBtns[i].className = 'toolbar-button toolbar-button--primary';
              newBtns[i].textContent = newBtns[i].getAttribute('value');
            } else {
              newBtns[i] = actionBtns[i].cloneNode(true);
            }
          }
          newBtns.reverse().forEach(function (item) {
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
