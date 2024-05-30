
// Example Drupal theme function for sticky header checkbox
Drupal.theme.stickyHeaderCheckbox = function () {
  return `<div class="tableheader-toggle-sticky">
            <label>
              <input type="checkbox" data-drupal-toggle-sticky-header checked/>
              Sticky Header
            </label>
          </div>`;
};

document.addEventListener('DOMContentLoaded', function () {
  const stickyHeaderElement = document.getElementsByClassName('views-table')[0];

  if (stickyHeaderElement) {
    stickyHeaderElement.insertAdjacentHTML('beforebegin', Drupal.theme.stickyHeaderCheckbox());

    const checkbox = document.querySelector('[data-drupal-toggle-sticky-header]');

    checkbox.addEventListener('change', function () {
      if (checkbox.checked) {
        if (!stickyHeaderElement.classList.contains('sticky-header')) {
          stickyHeaderElement.classList.add('sticky-header');
        }
      } else {
         if (stickyHeaderElement.classList.contains('sticky-header')) {
           stickyHeaderElement.classList.remove('sticky-header');
         }
      }
    });
  }
});
