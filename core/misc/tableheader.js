(function (Drupal, once) {
Drupal.theme.stickyHeaderCheckbox = function () {
  return `<div class="tableheader-toggle-sticky">
            <label>
              <input type="checkbox" data-drupal-toggle-sticky-header checked/>
              ${Drupal.t('Sticky Header')}
            </label>
          </div>`;
};

Drupal.behaviors.makeStikcyOptional = {
  attach(context) {
    const stickyHeaderElement = once('makeStikcyOptional', '.views-table', context).shift();
    if (stickyHeaderElement) {
      stickyHeaderElement.insertAdjacentHTML('beforebegin', Drupal.theme.stickyHeaderCheckbox());
      const previousElement = stickyHeaderElement.previousElementSibling;
      const checkbox = previousElement.querySelector('input[type="checkbox"]');
      checkbox.addEventListener('change', () => {
        stickyHeaderElement.classList.toggle('sticky-header', checkbox.checked);
      });
    }
  },
};
}(Drupal, once));
