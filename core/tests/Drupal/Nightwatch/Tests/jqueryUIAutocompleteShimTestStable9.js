// Runs jqueryUIAutocompleteShimTest.js but with Stable 9.

import jqueryUIAutocompleteShimTest from './jqueryUIAutocompleteShimTest';

console.log(jqueryUIAutocompleteShimTest);

module.exports = Object.assign(jqueryUIAutocompleteShimTest, {
  before(browser) {
    browser.drupalInstall().drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'jQuery Simulate')
        .waitForElementVisible(
          'input[name="modules[jquery_simulate][enable]"]',
          1000,
        )
        .click('input[name="modules[jquery_simulate][enable]"]')
        .click('input[type="submit"]')
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'autocomplete Shim Test')
        .waitForElementVisible(
          'input[name="modules[autocomplete_shim_test][enable]"]',
          1000,
        )
        .click('input[name="modules[autocomplete_shim_test][enable]"]')
        .click('input[type="submit"]');
      browser
        .drupalRelativeURL('/admin/appearance')
        .click(
          '[title="Install Test theme depending on Stable 9 as default theme"]',
        )
        .waitForElementVisible(
          '[data-drupal-messages] [role="contentinfo"]',
          10000,
        );
    });
  },
});
