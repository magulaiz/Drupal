/**
 * @file
 * Claro's enhancement for autocomplete form element.
 */

// cspell:ignore is-autocompleting

(($, Drupal, once) => {
  Drupal.behaviors.claroAutoCompete = {
    attach(context) {
      once('claroAutoComplete', 'input.form-autocomplete', context).forEach(
        (value) => {
          const $input = $(value);
          const classRemove = ($autoCompleteElem) => {
            $autoCompleteElem[0].classList.remove('is-autocompleting');
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
                  .siblings('[data-drupal-selector="autocomplete-message"]')[0]
                  .classList.remove('hidden');
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
