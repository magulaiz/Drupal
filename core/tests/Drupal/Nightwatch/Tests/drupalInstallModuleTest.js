module.exports = {
  '@tags': ['core'],

  before(browser) {
    browser
      .drupalInstall()
      .drupalInstallModule(['ckeditor5', 'field_ui'], true)
      .drupalInstallModule('dblog');
  },
  after(browser) {
    browser.drupalUninstall();
  },

  'Test drupalInstallModule': (browser) => {
    browser.drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .assert.not.enabled(
          'form.system-modules [name="modules[ckeditor5][enable]"]',
        )
        .assert.not.enabled(
          'form.system-modules [name="modules[field_ui][enable]"]',
        )
        .assert.not.enabled(
          'form.system-modules [name="modules[dblog][enable]"]',
        );
    });
  },
};
