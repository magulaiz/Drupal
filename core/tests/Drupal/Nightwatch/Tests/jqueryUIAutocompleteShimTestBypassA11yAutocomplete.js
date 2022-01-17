// cSpell:words unshimmed
// Runs jqueryUIAutocompleteShimTest.js but on a page where the input is not
// initialized to use the shim. jQuery UI autocomplete is instead used directly.
// Functionality is not re-routed to the shim. This is needed
// to confirm it's possible to use a contrib jQuery UI autocomplete while shim
// overrides are present.

import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

const modifiedShimTestBypassA11y = Object.assign(jqueryUIAutocompleteShimTest, {
  beforeEach(browser) {
    browser
      .drupalRelativeURL('/autocomplete-shim-test-bypass-a11y')
      .waitForElementPresent('#autocomplete-wrap1', 1000);
  },
  'the input is not shimmed': (browser) => {
    browser
      .drupalRelativeURL('/autocomplete-shim-test-bypass-a11y')
      .waitForElementPresent('#autocomplete-wrap1', 1000)
      .waitForElementPresent('#autocomplete.ui-autocomplete-input', 1000)
      .waitForElementNotPresent('#autocomplete[data-autocomplete-input]', 1000);
  },
  'ARIA, aria-label announcement': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        $.widget('custom.categoryComplete', $.ui.autocomplete, {
          // eslint-disable-next-line object-shorthand
          _renderMenu(ul, items) {
            const that = this;
            // eslint-disable-next-line func-names
            $.each(items, function (index, item) {
              that
                ._renderItemData(ul, item)
                .attr('aria-label', `${item.category} : ${item.label}`);
            });
          },
        });
        const $element = $('#autocomplete');
        $element.autocomplete('destroy');
        $element.categoryComplete({
          source: [{ label: 'Large Penguin', category: 'People' }],
        });
        $element.categoryComplete('search', 'a');

        return $element
          .categoryComplete('widget')
          .find('li')
          .attr('aria-label');
      },
      [],
      (result) => {
        browser.assert.equal(
          result.value,
          'People : Large Penguin',
          'Aria attribute updated as a result of extension point override',
        );
      },
    );
  },
});

module.exports = modifiedShimTestBypassA11y;
