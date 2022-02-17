/**
 * Creates role with given permissions.
 *
 * @param {string} module
 *   The list of modules to enable.
 * @param {function} callback
 *   A callback which will be called, when creating the role is finished.
 * @return {object}
 *   The drupalInstallModules command.
 */
exports.command = function drupalInstallModule(
  module,
  callback,
) {
  const self = this;
  this.drupalLoginAsAdmin(() => {
    this
      .drupalRelativeURL('/admin/modules')
      .click(`input[data-drupal-selector="edit-modules-${module}-enable"]`)
      .click('input[data-drupal-selector="edit-submit"]')
      // Wait for the install message to show up.
      .waitForElementVisible('.system-modules', 10000);
  }).perform(() => {
    if (typeof callback === 'function') {
      callback.call(self);
    }
  });

  return this;
};
