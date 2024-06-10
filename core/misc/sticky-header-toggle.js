(function (Drupal, once) {
  Drupal.theme.stickyHeaderCheckbox = function () {
    return `<div class="tableheader-toggle-sticky">
            <label>
              <input type="checkbox" data-drupal-toggle-sticky-header checked/>
              ${Drupal.t('Sticky Header')}
            </label>
          </div>`;
  };

  Drupal.behaviors.makeStickyOptional = {
    attach(context) {
      const stickyHeaderElement = once(
        'makeStickyOptional',
        'table.sticky-header',
        context,
      ).shift();
      if (stickyHeaderElement) {
        stickyHeaderElement.insertAdjacentHTML(
          'beforebegin',
          Drupal.theme.stickyHeaderCheckbox(),
        );
        // const previousElement = stickyHeaderElement.previousElementSibling;
        // const checkbox = previousElement.querySelector(
        //   'input[type="checkbox"]',
        // );
        const checkbox = document.querySelector(
          '[data-drupal-toggle-sticky-header]',
        );

        const stickyEnabled =
          !localStorage.getItem('stickyHeaderEnabled') ||
          localStorage.getItem('stickyHeaderEnabled') === 'true';
        checkbox.checked = stickyEnabled;
        stickyHeaderElement.classList.toggle('sticky-header', stickyEnabled);

        checkbox.addEventListener('change', () => {
          const isChecked = checkbox.checked;
          stickyHeaderElement.classList.toggle('sticky-header', isChecked);
          localStorage.setItem('stickyHeaderEnabled', isChecked);
        });
      }
    },
  };
})(Drupal, once);
