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
      browser
        .drupalRelativeURL('/admin/people/permissions')
        .click('input[name="anonymous[access toolbar]"]')
        .click('input[type="submit"]');
    });
  },
  beforeEach(browser) {
    browser
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
          toolbarModelisTrayToggleVisible: 'get("isTrayToggleVisible") has expected result',
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
    browser.execute(
      function(browser) {
        toReturn = {};
        const { models } = Drupal.toolbar;
              toReturn.hasMenuModel = models.hasOwnProperty('menuModel');
              toReturn.menuModelType = typeof models.menuModel === 'object';
              toReturn.hasToolbarModel = models.hasOwnProperty('toolbarModel');
              toReturn.toolbarModelType = typeof models.toolbarModel === 'object';
        const tab = document.getElementById('toolbar-item-user');
        tab.dispatchEvent(new MouseEvent('click', {bubbles:true}));
        browser.sleep(2000);

        // setTimeout(() => {
          toReturn.toolbaroffsetsconsole = models.toolbarModel.get('activeTab');
          toReturn.toolbaroffsetsconsole = models.toolbarModel.get('activeTray');

          // toReturn.toolbarModelActiveTab =
          //   models.toolbarModel.get('activeTab').outerHTML ===
          //   '<a href="/admin" title="Admin menu" class="toolbar-icon toolbar-icon-menu trigger toolbar-item is-active"' +
          //   'data-drupal-subtrees="" id="toolbar-item-administration" data-toolbar-tray="toolbar-item-administration-tray" ' +
          //   'role="button" aria-pressed="false">Manage</a>';
          // toReturn.toolbarModelActiveTray =
          //   models.toolbarModel.get('activeTray').outerHTML ===
          //   '<div id="toolbar-item-administration-tray" data-toolbar-tray="toolbar-item-administration-tray" ' +
          //   'class="toolbar-tray is-active toolbar-tray-horizontal" data-offset-top=""><nav class="toolbar-lining clearfix" ' +
          //   'role="navigation" aria-label="Administration menu"><h3 class="toolbar-tray-name visually-hidden">Administration menu</h3><div ' +
          //   'class="toolbar-menu-administration"></div><div class="toolbar-toggle-orientation" style="display: block;"><div class="toolbar-lining"><button ' +
          //   'class="toolbar-icon toolbar-icon-toggle-vertical" type="button" value="vertical" title="Vertical orientation">Vertical orientation</button></div></div></nav></div>';
          browser.waitForElementVisible('#I-DO-NOT-EXIST', 1000000000000000);

          return toReturn;
        // }, 5000);
      },
      [],
      (result) => {
        console.log('resultssssssss: ', result);
        const expectedTrue = {
          toolbarModelActiveTab: 'get("activeTab") has expected result',
          toolbarModelActiveTray: 'get("activeTray") has expected result',
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
    browser.execute(
      function(browser) {
        toReturn = {};
        const { models } = Drupal.toolbar;
        const orientationToggle = document.querySelector('toolbar-icon toolbar-icon-toggle-vertical');
        orientationToggle.dispatchEvent(new MouseEvent('click', {bubbles:true}));
        browser.sleep(2000);

        // setTimeout(() => {
        toReturn.toolbaroffsetsconsole = models.toolbarModel.get('orientation') === 'vertical';
        toReturn.toolbarModelActiveTab =
          models.toolbarModel.get('activeTab').outerHTML ===
          '<a href="/admin" title="Admin menu" class="toolbar-icon toolbar-icon-menu trigger toolbar-item is-active" data-drupal-subtrees="" id="toolbar-item-administration" data-toolbar-tray="toolbar-item-administration-tray" role="button" aria-pressed="false">Manage</a>';

        browser.waitForElementVisible('#I-DO-NOT-EXIST', 1000000000000000);

        return toReturn;
        // }, 5000);
      },
      [],
      (result) => {
        console.log('resultssssssss: ', result);
        const expectedTrue = {
          toolbarModelActiveTab: 'get("activeTab") has expected result',
          toolbarModelActiveTray: 'get("activeTray") has expected result',
          toolbarModelOrientation: 'get("orientation") has expected result',

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

  'check subtrees': (browser) => {
    browser.execute(
      function(browser) {
        toReturn = {};
        const { models } = Drupal.toolbar;
        const orientationToggle = document.querySelector('toolbar-icon toolbar-icon-toggle-vertical');
        orientationToggle.dispatchEvent(new MouseEvent('click', {bubbles:true}));
        browser.sleep(2000);

        // setTimeout(() => {
        toReturn.toolbaroffsetsconsole = models.toolbarModel.get('orientation') === 'vertical';
        toReturn.toolbarModelActiveTab =
          models.toolbarModel.get('activeTab').outerHTML ===
          '<a href="/admin" title="Admin menu" class="toolbar-icon toolbar-icon-menu trigger toolbar-item is-active" data-drupal-subtrees="" id="toolbar-item-administration" data-toolbar-tray="toolbar-item-administration-tray" role="button" aria-pressed="false">Manage</a>';
        toReturn.toolbaroff
          = models.toolbarModel.get('orientation') === 'vertical';


        return toReturn;
      },
      [],
      (result) => {
        console.log('resultssssssss: ', result);
        const expectedTrue = {
          toolbarModelActiveTab: 'get("activeTab") has expected result',
          toolbarModelActiveTray: 'get("activeTray") has expected result',
          toolbarModelOrientation: 'get("orientation") has expected result',

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
