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
    browser.resizeWindow(1920, 1080);
    browser.execute(function () {
      // To clear active tab/tray from previous tests
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
    browser.assert.not.cssClassPresent('#toolbar-item-user', 'is-active');
    browser.assert.not.cssClassPresent('#toolbar-item-user-tray', 'is-active');
    browser.click('#toolbar-item-user');
    browser.assert.cssClassPresent('#toolbar-item-user', 'is-active');
    browser.assert.cssClassPresent('#toolbar-item-user-tray', 'is-active');
  },
  'Change orientation': (browser) => {
    const orientationButton =
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button';
    browser.waitForElementPresent(orientationButton);
    browser.assert.cssClassPresent('#toolbar-item-administration-tray', [
      'is-active',
      'toolbar-tray-horizontal',
    ]);
    browser.click(orientationButton);
    browser.assert.cssClassPresent('#toolbar-item-administration-tray', [
      'is-active',
      'toolbar-tray-vertical',
    ]);
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
      .not.contain('toolbar-fixed')
      .before(300);
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
  'Back to site link': (browser) => {
    const escapeSelector = '[data-toolbar-escape-admin]';
    browser.drupalRelativeURL('/user');
    browser.drupalRelativeURL('/admin');
    // Don't check the visibility as stark doesn't add the .path-admin class
    // to the <body> required to display the button.
    browser.assert.attributeContains(escapeSelector, 'href', '/user/2');
  },
  'Aural view test': (browser) => {
    browser.executeAsync(
      function (done) {
        Drupal.announce = done;

        const orientationButton = document.querySelector(
          '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
        );
        orientationButton.dispatchEvent(
          new MouseEvent('click', { bubbles: true }),
        );
      },
      (result) => {
        browser.assert.equal(
          result.value,
          'Tray orientation changed to vertical.',
        );
      },
    );
    browser.executeAsync(
      function (done) {
        Drupal.announce = done;

        const orientationButton = document.querySelector(
          '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
        );
        orientationButton.dispatchEvent(
          new MouseEvent('click', { bubbles: true }),
        );
      },
      (result) => {
        browser.assert.equal(
          result.value,
          'Tray orientation changed to horizontal.',
        );
      },
    );
  },
  'Toolbar event: drupalToolbarOrientationChange': (browser) => {
    browser.executeAsync(
      function (done) {
        jQuery(document).on(
          'drupalToolbarOrientationChange',
          function (event, orientation) {
            done(orientation);
          },
        );
        const orientationButton = document.querySelector(
          '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
        );
        orientationButton.dispatchEvent(
          new MouseEvent('click', { bubbles: true }),
        );
      },
      (result) => {
        browser.assert.equal(result.value, 'vertical');
      },
    );
  },
  'Toolbar event: drupalToolbarTabChange': (browser) => {
    browser.executeAsync(
      function (done) {
        jQuery(document).on('drupalToolbarTabChange', function (event, tab) {
          done(tab.id);
        });
        jQuery('#toolbar-item-user').trigger('click');
      },
      (result) => {
        browser.assert.equal(result.value, 'toolbar-item-user');
      },
    );
  },
  'Toolbar event: drupalToolbarTrayChange': (browser) => {
    browser.executeAsync(
      function (done) {
        const $adminButton = jQuery('#toolbar-item-administration');
        // Hide the admin menu first, this event is not firing reliably otherwise.
        $adminButton.trigger('click');
        jQuery(document).on('drupalToolbarTrayChange', function (event, tray) {
          done(tray.id);
        });
        $adminButton.trigger('click');
      },
      (result) => {
        browser.assert.equal(result.value, 'toolbar-item-administration-tray');
      },
    );
  },
  'Locked toolbar vertical wide viewport': (browser) => {
    browser.resizeWindow(1000, 900);
    browser.waitForElementPresent(
      '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
    );
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
    browser.expect
      .element(
        '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
      )
      .to.not.be.visible.before(100);
  },
  'Settings are retained on refresh': (browser) => {
    browser.waitForElementPresent('#toolbar-item-user');
    const toggleOrientationBtn =
      '#toolbar-item-user-tray .toolbar-toggle-orientation button';
    // Set user as active tab
    browser.expect
      .element('#toolbar-item-user')
      .to.have.property('className')
      .not.contain('is-active');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .not.contain('is-active');
    browser.click('#toolbar-item-user');
    // Check tray is open
    browser.expect
      .element('#toolbar-item-user')
      .to.have.property('className')
      .contain('is-active');
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active');
    // Set orientation to vertical
    browser.waitForElementPresent(toggleOrientationBtn);
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-horizontal');
    browser.click(toggleOrientationBtn);
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical');
    browser.refresh();
    // Check user tray is open
    browser.expect
      .element('#toolbar-item-user')
      .to.have.property('className')
      .contain('is-active');
    // Check orientation is vertical
    browser.expect
      .element('#toolbar-item-user-tray')
      .to.have.property('className')
      .contain('is-active')
      .contain('toolbar-tray-vertical');
  },
  'Check toolbar overlap with page content': (browser) => {
    browser.execute(
      () => {
        const toolbar = document.querySelector('#toolbar-administration');
        const nextElement = toolbar.nextElementSibling.getBoundingClientRect();
        const tray = document
          .querySelector('#toolbar-item-administration-tray')
          .getBoundingClientRect();
        // Page content should start after the toolbar height to not overlap.
        return nextElement.top > tray.top + tray.height;
      },
      (result) => {
        browser.assert.equal(
          result.value,
          true,
          'Toolbar and page content do not overlap',
        );
      },
    );
  },
};
