// Runs jqueryUIAutocompleteShimTest.js but jQuery UI autocomplete is used
// directly, without functionality being re-routed to the shim. This is needed
// to confirm it's possible to use a contrib jQuery UI autocomplete while shim
// overrides are present.

import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

const modifiedShimTest = Object.assign(jqueryUIAutocompleteShimTest, {
  beforeEach(browser) {
    browser
      .drupalRelativeURL('/autocomplete-shim-test-bypass-a11y')
      .waitForElementPresent('#autocomplete-wrap1', 1000);
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
