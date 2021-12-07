// cSpell:words unshimmed
// This file Runs the tests in jqueryUIAutocompleteShimTest.js with the
// following use case:
// - It is on a page where both a shimmed and unshimmed input are present.
// - The tests are all performed on the unshimmed input.
import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

const modifiedShimTestTestDirect = Object.assign(jqueryUIAutocompleteShimTest, {
  beforeEach(browser) {
    browser
      .drupalRelativeURL(
        '/autocomplete-shim-test-with-additional-direct-jquery-as-primary-input',
      )
      .waitForElementPresent('#autocomplete', 1000)
      .waitForElementPresent('#direct-jquery[data-autocomplete-input]', 1000)
      .waitForElementNotPresent('#autocomplete.form-autocomplete');
  },
  'widget factory extend ui.autocomplete with custom _renderMenu': (
    browser,
  ) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        $('#autocomplete-wrap1').append('<input id="autocomplete-extend" />');

        $.widget('ui.autocomplete', $.ui.autocomplete, {
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
        const $element = $('#autocomplete-extend').autocomplete({
          source: [{ label: 'Large Penguin', category: 'People' }],
        });
        $element.autocomplete('search', 'a');

        return $element.autocomplete('widget').find('li').attr('aria-label');
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

module.exports = modifiedShimTestTestDirect;
