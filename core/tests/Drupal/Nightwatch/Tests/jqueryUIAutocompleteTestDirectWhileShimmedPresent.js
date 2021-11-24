// cSpell:words unshimmed
// This file Runs the tests in jqueryUIAutocompleteShimTest.js with the
// following use case:
// - It is on a page where both a shimmed and unshimmed input are present.
// - The tests are all performed on the unshimmed input.
import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

const modifiedShimTest = Object.assign(jqueryUIAutocompleteShimTest, {
  beforeEach(browser) {
    browser
      .drupalRelativeURL(
        '/autocomplete-shim-test-with-additional-direct-jquery-as-primary-input',
      )
      .waitForElementPresent('#autocomplete', 1000)
      .execute(
        // eslint-disable-next-line func-names, prefer-arrow-callback
        function () {
          // jQuery('#direct-jquery').autocomplete();
        },
      )
      .waitForElementPresent('#direct-jquery[data-autocomplete-input]', 1000)
      .waitForElementNotPresent('#autocomplete.form-autocomplete');
  },
  after(browser) {
    browser
      .waitForElementNotPresent('[data-autocomplete-wrapper]', 1000)
      .drupalUninstall();
  },
});

// Remove tests because unsupported widget use warnings will not happen on
// pages where jQuery UI autocomplete is loaded.
delete modifiedShimTest['unsupported use of custom widget'];

// Remove deprecation test. The warning should not fire on inputs without
// .form-autocomplete, as they are given the option of accessing a non-core
// autocomplete without warnings.
delete modifiedShimTest['test deprecation'];

module.exports = modifiedShimTest;
