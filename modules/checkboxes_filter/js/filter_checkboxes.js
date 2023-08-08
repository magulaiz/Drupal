(function ($, Drupal) {
  Drupal.behaviors.checkboxesFilter = {
    attach: function (context, settings) {
      var $checkboxesElement = $('.checkboxes-filter-element', context);
      function filterCheckboxes (filterText, $element) {
        $('div.form-item.form-type-checkbox', $element).each(function() {
          if (filterText === '') {
            $(this).show();
          } else {
            let labelText = $(this).find('label').html();
            if (labelText.toLowerCase().indexOf(filterText.toLowerCase()) >= 0) {
              $(this).show();
            } else {
              $(this).hide();
            }
          }
        });
      }
      $checkboxesElement.once('checkboxes-filter').each(function () {
        let currentElement = $(this);
        currentElement.prepend(
          $('<input type="text" class="form-text" placeholder="' + Drupal.t('Filter') + '">').on('blur', function () {
            filterCheckboxes($(this).val(), currentElement);
          }).on('keyup', function () {
            filterCheckboxes($(this).val(), currentElement);
          })
        );
      });
    },
  };
})(jQuery, Drupal);
