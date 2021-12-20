module.exports = {
  '@tags': ['core'],
  before(browser) {
    browser.drupalInstall().drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'toolbar')
        .waitForElementVisible('input[name="modules[toolbar][enable]"]', 1000)
        .click('input[name="modules[breakpoint][enable]"]')
        .click('input[name="modules[toolbar][enable]"]')
        .click('input[type="submit"]');
      // browser
      //   .drupalRelativeURL('/admin/people/permissions')
      //   .click('input[name="anonymous[access toolbar]"]')
      //   .click('input[type="submit"]');
    });
    browser
      .drupalCreateUser({
        name: 'user',
        password: '123',
        // add more permissions to have more stuff in the toolbar.
        permissions: [
          'access site reports',
          'access toolbar',
          'administer menu',
          'administer modules',
          'administer site configuration',
          // 'Administer modules',
          'administer account settings',
          'administer software updates',
          'access content',
          'administer permissions',
          'administer users',
        ],
      })
      .drupalLogin({ name: 'user', password: '123' })
      .drupalRelativeURL('/')
      .waitForElementPresent('.toolbar-item.is-active', 10000);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'Change tab': (browser) => {
    browser.drupalRelativeURL('/')
      .waitForElementPresent('#toolbar-item-user-tray');
    browser.expect.element('#toolbar-item-user-tray').to.have.property('className').not.contain('is-active');
    browser.click('#toolbar-item-user');
    browser.expect.element('#toolbar-item-user-tray').to.have.property('className').contain('is-active').before(500);
  },
  'Change orientation': (browser) => {},
  'Toggle tray': (browser) => {},
  'Toggle submenu': (browser) => {},
  'Narrow toolbar': (browser) => {},
  'Standard width toolbar': (browser) => {},
  'Wide toolbar': (browser) => {},
  'Back to site link': (browser) => {},
  'Aural view test': (browser) => {},
  'Toolbar events': (browser) => {},
  'Locked toolbar vertical wide viewport': (browser) => {},
  'Settings are retained on retained': (browser) => {},
};
