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
      .waitForElementPresent('#direct-jquery[data-autocomplete-input]', 1000)
      .waitForElementNotPresent('#autocomplete.form-autocomplete');
  },
  after(browser) {
    browser
      .waitForElementNotPresent('[data-autocomplete-wrapper]', 1000)
      .drupalUninstall();
  },
});

module.exports = modifiedShimTest;
