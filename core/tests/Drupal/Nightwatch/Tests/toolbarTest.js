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
    });
    browser
      .drupalCreateUser({
        name: 'user',
        password: '123',
        permissions: [
          'access site reports',
          'access toolbar',
          'access administration pages',
          'administer menu',
          'administer modules',
          'administer site configuration',
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
  beforeEach(browser) {
    // To clear active tab/tray from previous tests
    browser.execute(function () {
      localStorage.clear();
    });
    browser
      .drupalRelativeURL('/')
      .waitForElementPresent('.toolbar-item.is-active', 10000);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'Change tab': (browser) => {
    browser.waitForElementPresent('#toolbar-item-user-tray');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .not.contain('is-active');
    browser.click('#toolbar-item-user');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active')
      .before(100);
  },
  'Change orientation': (browser) => {
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-horizontal');
    browser.click(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
  },
  'Toggle tray': (browser) => {
    browser.waitForElementPresent('#toolbar-item-user-tray');
    browser.click('#toolbar-item-user');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active');
    browser.click('#toolbar-item-user');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .not.contain('is-active')
      .before(100);
    browser.click('#toolbar-item-user');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active')
      .before(100);
  },
  'Toggle submenu and sub-submenu': (browser) => {
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-horizontal');
    browser.click(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
    // browser.expect
    //   .element('#toolbar-item-administration-tray li:nth-child(4) button')
    //   .to.have.property('className')
    //   .not.contain('open')
    //   .before(500);
    browser.expect
      .element('#toolbar-item-administration-tray li:nth-child(4)')
      .to.have.property('className')
      .not.contain('open');
    browser.click('#toolbar-item-administration-tray li:nth-child(4) button');
    // browser.expect
    //   .element('#toolbar-item-administration-tray li:nth-child(4) button')
    //   .to.have.property('className')
    //   .contain('open')
    //   .before(100);
    browser.expect
      .element('#toolbar-item-administration-tray li:nth-child(4)')
      .to.have.property('className')
      .contain('open')
      .before(200);
    browser.expect
      .element('#toolbar-link-user-admin_index')
      .text.to.equal('People');
    browser.expect
      .element('#toolbar-link-system-admin_config_system')
      .text.to.equal('System');
    // Check sub-submenu
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2')
      .to.have.property('className')
      .not.contain('open');
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2')
      .to.have.property('className')
      .not.contain('open');
    // browser.expect
    //   .element('#toolbar-item-administration-tray li.menu-item.level-2 button')
    //   .to.have.property('className')
    //   .not.contain('open');
    browser.click(
      '#toolbar-item-administration-tray li.menu-item.level-2 button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2')
      .to.have.property('className')
      .contain('open')
      .before(200);
    // browser.expect
    //   .element('#toolbar-item-administration-tray li.menu-item.level-2 button')
    //   .to.have.property('className')
    //   .contain('open');
    browser.expect
      .element('#toolbar-link-entity-user-admin_form')
      .text.to.equal('Account settings');
  },
  'Narrow toolbar': (browser) => {
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-horizontal');
    // browser.resizeWindow(x, y)

    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);

  },
  'Standard width toolbar': (browser) => {
    browser.resizeWindow(x, y)
  },
  'Wide toolbar': (browser) => {},
  'Back to site link': (browser) => {},
  'Aural view test': (browser) => {},
  'Toolbar events': (browser) => {},
  'Locked toolbar vertical wide viewport': (browser) => {},
  'Settings are retained on retained': (browser) => {},
};
