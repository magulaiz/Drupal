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
        const previousElement = stickyHeaderElement.previousElementSibling;
        const checkbox = previousElement.querySelector(
          'input[type="checkbox"]',
        );

        // Ensure the checkbox is in view and not overlapped by other elements.
        const scrollIntoViewIfNeeded = (element) => {
          const rect = element.getBoundingClientRect();
          if (
            rect.top < 0 ||
            rect.left < 0 ||
            rect.bottom >
              (window.innerHeight || document.documentElement.clientHeight) ||
            rect.right >
              (window.innerWidth || document.documentElement.clientWidth)
          ) {
            element.scrollIntoView({
              behavior: 'smooth',
              block: 'center',
              inline: 'center',
            });
          }
        };
        const stickyEnabled =
          !localStorage.getItem('stickyHeaderEnabled') ||
          localStorage.getItem('stickyHeaderEnabled') === 'true';
        checkbox.checked = stickyEnabled;
        stickyHeaderElement.classList.toggle('sticky-header', stickyEnabled);

        checkbox.addEventListener('change', () => {
          const isChecked = checkbox.checked;
          stickyHeaderElement.classList.toggle('sticky-header', isChecked);
          localStorage.setItem('stickyHeaderEnabled', isChecked.toString());
        });

        // Ensure checkbox is clickable and in view
        scrollIntoViewIfNeeded(checkbox);

        // Add a slight delay to ensure the checkbox is rendered properly
        setTimeout(() => {
          scrollIntoViewIfNeeded(checkbox);
        }, 200);
      }
    },
  };
})(Drupal, once);
