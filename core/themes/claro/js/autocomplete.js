/**
 * @file
 * Claro's enhancement for autocomplete form element.
 */

(($, Drupal, once) => {
  Drupal.behaviors.claroAutoCompete = {
    attach(context) {
      once('claroAutoComplete', 'input.form-autocomplete', context).forEach(
        (value) => {
          const $input = $(value);
          const classRemove = ($autoCompleteElem) => {
            $autoCompleteElem.removeClass('is-autocompleting');
            $autoCompleteElem
              .siblings('[data-drupal-selector="autocomplete-message"]')[0]
              .classList.add('hidden');
          };

          $input.autocomplete({
            search(event) {
              const result = Drupal.autocomplete.options.search(event);
              if (result) {
                event.target.classList.add('is-autocompleting');
                $(event.target)
                  .siblings('[data-drupal-selector="autocomplete-message"]')
                  .removeClass('hidden');
              }

              return result;
            },
            response(event) {
              classRemove($(event.target));
            },
          });
        },
      );
    },
  };
})(jQuery, Drupal, once);
