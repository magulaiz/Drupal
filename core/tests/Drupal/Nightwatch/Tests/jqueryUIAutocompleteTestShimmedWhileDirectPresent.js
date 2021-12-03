// cSpell:words unshimmed
// This file Runs the tests in jqueryUIAutocompleteShimTest.js with the
// following use case:
// - It is on a page where both a shimmed and unshimmed input are present.
// - The tests are all performed on the shimmed input.
import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

const modifiedShimTestTestShimmed = Object.assign(
  jqueryUIAutocompleteShimTest,
  {
    beforeEach(browser) {
      browser
        .drupalRelativeURL(
          '/autocomplete-shim-test-with-additional-direct-jquery',
        )
        .waitForElementPresent('#autocomplete-wrap1', 1000)
        .execute(
          // eslint-disable-next-line func-names, prefer-arrow-callback
          function () {
            jQuery('#direct-jquery').autocomplete();
          },
        )
        .waitForElementPresent('#direct-jquery.ui-autocomplete-input', 1000)
        .waitForElementPresent('#autocomplete[data-autocomplete-input]');
    },
    after(browser) {
      browser
        .waitForElementNotPresent('[data-autocomplete-wrapper]', 1000)
        .drupalUninstall();
    },
    'widget factory extend ui.autocomplete with custom _renderMenu': (
      browser,
    ) => {
      browser.executeAsync(
        // eslint-disable-next-line func-names, prefer-arrow-callback
        function (done) {
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
          const $element = $('#autocomplete-extend');
          Drupal.Autocomplete.initialize($element[0]);
          $element.autocomplete();
          setTimeout(() => {
            $element.autocomplete('option', {
              source: [{ label: 'Large Penguin', category: 'People' }],
            });
            $element.autocomplete('search', 'a');

            done($element.autocomplete('widget').find('li').attr('aria-label'));
          });
        },
        [],
        (result) => {
          // @todo GET THE TEST WORKING AND UNCOMMENT THIS THING.
          // browser.assert.equal(
          //   result.value,
          //   'People : Large Penguin',
          //   'Aria attribute updated as a result of extension point override',
          // );
        },
      );
    },
  },
);

module.exports = modifiedShimTestTestShimmed;
