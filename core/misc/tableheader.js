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

      const isStickyHeaderEnabled = localStorage.getItem('stickyHeaderEnabled') === 'true';
      checkbox.checked = isStickyHeaderEnabled;
      stickyHeaderElement.classList.toggle('sticky-header', isStickyHeaderEnabled);

      checkbox.addEventListener('change', () => {
        const isChecked = checkbox.checked;
        stickyHeaderElement.classList.toggle('sticky-header', isChecked);
        localStorage.setItem('stickyHeaderEnabled', isChecked);
      });
    }
  },
};
}(Drupal, once));
