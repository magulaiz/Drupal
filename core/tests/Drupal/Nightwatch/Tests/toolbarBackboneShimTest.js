module.exports = {
  '@tags': ['core'],
  before(browser) {
    browser.drupalInstall().drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'toolbar')
        .waitForElementVisible(
          'input[name="modules[toolbar][enable]"]',
          1000,
        )
        .click('input[name="modules[breakpoint][enable]"]')
        .click('input[name="modules[toolbar][enable]"]')
        .click('input[type="submit"]');
      browser
        .drupalRelativeURL('/admin/people/permissions')
        .click('input[name="anonymous[access toolbar]"]')
        .click('input[type="submit"]');
    });
  },
  beforeEach(browser) {
    browser
      .drupalRelativeURL('/')
      .waitForElementPresent('.toolbar-item.is-active', 10000000000);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'Drupal.Toolbar.models':  (browser) => {
    browser.execute(
      function() {
        toReturn = {};
        const models = Drupal.toolbar.models;
        toReturn.hasMenuModel = models.hasOwnProperty('menuModel');
        toReturn.menuModelType = typeof models.menuModel === 'object';
        toReturn.hasToolbarModel = models.hasOwnProperty('toolbarModel');
        toReturn.toolbarModelType = typeof models.toolbarModel === 'object';
        toReturn.toolbarModelActiveTab = models.toolbarModel.get('activeTab').outerHTML === '<a href="/admin" title="Admin menu" class="toolbar-icon toolbar-icon-menu trigger toolbar-item is-active" data-drupal-subtrees="" id="toolbar-item-administration" data-toolbar-tray="toolbar-item-administration-tray" role="button" aria-pressed="false">Manage</a>';

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          hasMenuModel: 'has menu model',
          hasToolbarModel: 'has toolbar model',
          toolbarModelActiveTab: 'get("activeTab") has expected result',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
};