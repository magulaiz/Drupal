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
  },
  beforeEach(browser) {
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
  'Drupal.Toolbar.models': (browser) => {
    browser.execute(
      function () {
        toReturn = {};
        const { models } = Drupal.toolbar;
        toReturn.hasMenuModel = models.hasOwnProperty('menuModel');
        toReturn.menuModelType = typeof models.menuModel === 'object';
        toReturn.hasToolbarModel = models.hasOwnProperty('toolbarModel');
        toReturn.toolbarModelType = typeof models.toolbarModel === 'object';
        toReturn.toolbarModelActiveTab =
          models.toolbarModel.get('activeTab').outerHTML ===
          '<a href="/admin" title="Admin menu" class="toolbar-icon toolbar-icon-menu trigger toolbar-item is-active" data-drupal-subtrees="" id="toolbar-item-administration" data-toolbar-tray="toolbar-item-administration-tray" role="button" aria-pressed="false">Manage</a>';
        toReturn.toolbarModelActiveTray =
          models.toolbarModel.get('activeTray').outerHTML ===
          '<div id="toolbar-item-administration-tray" data-toolbar-tray="toolbar-item-administration-tray" class="toolbar-tray is-active toolbar-tray-horizontal" data-offset-top=""><nav class="toolbar-lining clearfix" role="navigation" aria-label="Administration menu"><h3 class="toolbar-tray-name visually-hidden">Administration menu</h3><div class="toolbar-menu-administration"></div><div class="toolbar-toggle-orientation" style="display: block;"><div class="toolbar-lining"><button class="toolbar-icon toolbar-icon-toggle-vertical" type="button" value="vertical" title="Vertical orientation">Vertical orientation</button></div></div></nav></div>';
        toReturn.toolbarModelisOriented =
          models.toolbarModel.get('isOriented') === true;
        toReturn.toolbarModelisFixed =
          models.toolbarModel.get('isFixed') === true;
        toReturn.toolbarModelareSubtreesLoaded =
          models.toolbarModel.get('areSubtreesLoaded') === false;
        toReturn.toolbarModelisViewportOverflowConstrained =
          models.toolbarModel.get('isViewportOverflowConstrained') === false;
        toReturn.toolbarModelOrientation =
          models.toolbarModel.get('orientation') === 'horizontal';
        toReturn.toolbarModelLocked =
          models.toolbarModel.get('locked') === null;
        toReturn.toolbarModelisTrayToggleVisible =
          models.toolbarModel.get('isTrayToggleVisible') === true;
        toReturn.toolbarModelHeight = models.toolbarModel.get('height') === 40;
        // toReturn.toolbarModelOffsets =
        //   models.toolbarModel.get('offsets') === { bottom: 0, left: 0, right: 0, top: 40 };
        toReturn.toolbarModelSubtrees =
          models.toolbarModel.get('subtrees') === {};
        return toReturn;
      },
      [],
      (result) => {
        console.log('results: ', result);
        const expectedTrue = {
          hasMenuModel: 'has menu model',
          hasToolbarModel: 'has toolbar model',
          toolbarModelActiveTab: 'get("activeTab") has expected result',
          toolbarModelActiveTray: 'get("activeTray") has expected result',
          toolbarModelisOriented: 'get("isOriented") has expected result',
          toolbarModelisFixed: 'get("isFixed") has expected result',
          toolbarModelareSubtreesLoaded:
            'get("areSubtreesLoaded") has expected result',
          toolbarModelisViewportOverflowConstrained:
            'get("isViewportOverflowConstrained") has expected result',
          toolbarModelOrientation: 'get("orientation") has expected result',
          toolbarModelLocked: 'get("locked") has expected result',
          toolbarModelisTrayToggleVisible:
            'get("isTrayToggleVisible") has expected result',
          toolbarModelHeight: 'get("height") has expected result',
          toolbarModelOffsets: 'get("offsets") has expected result',
          toolbarModelSubtrees: 'get("subtrees") has expected result',
          toolbarModelChangeActiveTab: 'get("activeTab") has expected result',
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

  'Change tab': (browser) => {
    browser.executeAsync(
      function (done) {
        toReturn = {};
        const { models } = Drupal.toolbar;
        toReturn.hasMenuModel = models.hasOwnProperty('menuModel');
        toReturn.menuModelType = typeof models.menuModel === 'object';
        toReturn.hasToolbarModel = models.hasOwnProperty('toolbarModel');
        toReturn.toolbarModelType = typeof models.toolbarModel === 'object';

        setTimeout(() => {
          const tab = document.querySelector('#toolbar-item-user');
          tab.dispatchEvent(new MouseEvent('click', { bubbles: true }));

          toReturn.toolbarModelChangedTab =
            models.toolbarModel.get('activeTab').outerHTML ===
            '<a href="/user" title="My account" class="toolbar-icon toolbar-icon-user trigger toolbar-item is-active" id="toolbar-item-user" data-toolbar-tray="toolbar-item-user-tray" role="button" aria-pressed="false">user</a>';
          toReturn.toolbarModelChangedTray =
            models.toolbarModel.get('activeTray').outerHTML ===
            '<div id="toolbar-item-user-tray" data-toolbar-tray="toolbar-item-user-tray" class="toolbar-tray toolbar-tray-horizontal is-active" data-offset-top=""><nav class="toolbar-lining clearfix" role="navigation" aria-label="User account actions"><h3 class="toolbar-tray-name visually-hidden">User account actions</h3><ul class="toolbar-menu"><li><a href="/user" title="User account">View profile</a></li><li><a href="/user/2/edit" title="Edit user account">Edit profile</a></li><li><a href="/user/logout">Log out</a></li></ul><div class="toolbar-toggle-orientation" style="display: block;"><div class="toolbar-lining"><button class="toolbar-icon toolbar-icon-toggle-vertical" type="button" value="vertical" title="Vertical orientation">Vertical orientation</button></div></div></nav></div>';
          done(toReturn);
        }, 100);
      },
      [],
      (result) => {
        const expectedTrue = {
          toolbarModelChangedTab: 'get("activeTab") has expected result',
          toolbarModelChangedTray: 'get("activeTray") has expected result',
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

  'Change orientation': (browser) => {
    browser.executeAsync(
      function (done) {
        toReturn = {};
        const { models } = Drupal.toolbar;
        const orientationToggle = document.querySelector(
          '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
        );
        toReturn.toolbarOrientation =
          models.toolbarModel.get('orientation') === 'horizontal';
        orientationToggle.dispatchEvent(
          new MouseEvent('click', { bubbles: true }),
        );
        setTimeout(() => {
          toReturn.toolbarChangeOrientation =
            models.toolbarModel.get('orientation') === 'vertical';
          done(toReturn);
        }, 100);
      },
      [],
      (result) => {
        console.log('resultssssssss: ', result);
        const expectedTrue = {
          toolbarOrientation: 'get("orientation") has expected result',
          toolbarChangeOrientation: 'changing orientation has expected result',
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
    // browser.waitForElementVisible('#I-DO-NOT-EXIST', 1000000000000000);
  },
  'Open submenu': (browser) => {
    browser.executeAsync(
      function (done) {
        toReturn = {};
        const { models } = Drupal.toolbar;
        const orientationToggle = document.querySelector(
          '#toolbar-item-administration-tray .toolbar-toggle-orientation button',
        );
        toReturn.toolbarOrientationn = models.toolbarModel.get('orientation');
        toReturn.toolbarOrientation =
          models.toolbarModel.get('orientation') === 'horizontal';
        orientationToggle.dispatchEvent(
          new MouseEvent('click', { bubbles: true }),
        );
        setTimeout(() => {
          toReturn.toolbarChangeOrientation =
            models.toolbarModel.get('orientation') === 'vertical';
          const menuDropdown = document.querySelector(
            '#toolbar-item-administration-tray > nav > div.toolbar-menu-administration > ul > li.menu-item.menu-item--collapsed.level-1 > div > button',
          );
          menuDropdown.dispatchEvent(
            new MouseEvent('click', { bubbles: true }),
          );
          const statReportElement = document.querySelector(
            '#toolbar-link-system-status',
          );
          toReturn.submenuItemmm = statReportElement.textContent;
          toReturn.submenuItem =
            statReportElement.textContent === 'Status report';

          done(toReturn);
        }, 100);
      },
      [],
      (result) => {
        console.log('resultssssssss: ', result);
        const expectedTrue = {
          toolbarOrientation: 'get("orientation") has expected result',
          toolbarChangeOrientation: 'changing orientation has expected result',
          submenuItem: 'opening submenu has expected result',
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
    // browser.waitForElementVisible('#I-DO-NOT-EXIST', 1000000000000000);
  },
};
