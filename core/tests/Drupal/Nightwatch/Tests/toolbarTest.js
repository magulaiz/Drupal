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
      .waitForElementPresent('#toolbar-administration', 10000);
  },
  beforeEach(browser) {
    // Set the resolution to the default desktop resolution. Ensure the default
    // toolbar is horizontal in headless mode.
    browser.resizeWindow(1920, 1080)
    // To clear active tab/tray from previous tests
    browser.execute(function () {
      localStorage.clear();
      // Clear escapeAdmin url values.
      sessionStorage.clear();
    });
    browser.drupalRelativeURL('/');
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
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray li:nth-child(4) button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray li:nth-child(4) button')
      .to.have.property('className')
      .not.contain('open')
      .before(500);
    browser.expect
      .element('#toolbar-item-administration-tray li:nth-child(4)')
      .to.have.property('className')
      .not.contain('open');
    browser.click('#toolbar-item-administration-tray li:nth-child(4) button');
    browser.expect
      .element('#toolbar-item-administration-tray li:nth-child(4) button')
      .to.have.property('className')
      .contain('open')
      .before(100);
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
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray li.menu-item.level-2',
    );
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2')
      .to.have.property('className')
      .not.contain('open');
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2 button')
      .to.have.property('className')
      .not.contain('open');
    browser.click(
      '#toolbar-item-administration-tray li.menu-item.level-2 button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2')
      .to.have.property('className')
      .contain('open')
      .before(200);
    browser.expect
      .element('#toolbar-item-administration-tray li.menu-item.level-2 button')
      .to.have.property('className')
      .contain('open');
    browser.expect
      .element('#toolbar-link-entity-user-admin_form')
      .text.to.equal('Account settings');
  },
  'Narrow toolbar width breakpoint': (browser) => {
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-horizontal');
    browser.expect
      .element('#toolbar-administration')
      .to.have.property('className')
      .contain('toolbar-oriented');
    browser.resizeWindow(263, 900);
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
    browser.expect
      .element('#toolbar-administration')
      .to.have.property('className')
      .not.contain('toolbar-oriented')
      .before(100);
  },
  'Standard width toolbar breakpoint': (browser) => {
    browser.resizeWindow(1000, 900);
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.expect
      .element('body')
      .to.have.property('className')
      .contain('toolbar-fixed');
    browser.resizeWindow(609, 900);
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
    browser.expect
      .element('body')
      .to.have.property('className')
      .not.contain('toolbar-fixed').before(300);
  },
  'Wide toolbar breakpoint': (browser) => {
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    browser.resizeWindow(975, 900);
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
  },
  'Back to site link': (browser) => {},
  'Aural view test': (browser) => {},
  'Toolbar events': (browser) => {},
  'Locked toolbar vertical wide viewport': (browser) => {
    browser.resizeWindow(1000, 900);
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
    // browser.expect
    //   .element(
    //     '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    //   )
    //   .to.have.css('display')
    //   .which.not.equals('none');
    browser.expect
      .element(
        '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
      )
      .to.be.visible.before(100);
    browser.resizeWindow(975, 900);
    browser.expect
      .element('#toolbar-item-administration-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical')
      .before(100);
    // browser.expect
    //   .element(
    //     '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    //   )
    //   .to.have.css('display')
    //   .which.equals('none')
    //   .before(400);
    browser.expect
      .element(
        '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
      )
      .to.not.be.visible.before(100);
  },
  'Settings are retained on refresh': (browser) => {},
};
