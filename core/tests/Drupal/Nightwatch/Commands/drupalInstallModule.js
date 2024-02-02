/**
 * Install the given module.
 *
 * @param {string} module
 *   The module machine name to enable.
<<<<<<< HEAD
=======
 * @param {boolean} force
 *   Force to install dependencies if applicable.
>>>>>>> upstream/11.x
 * @param {function} callback
 *   A callback which will be called, when the module has been enabled.
 * @return {object}
 *   The drupalInstallModule command.
 */
<<<<<<< HEAD
exports.command = function drupalInstallModule(module, callback) {
=======
exports.command = function drupalInstallModule(module, force, callback) {
>>>>>>> upstream/11.x
  const self = this;
  this.drupalLoginAsAdmin(() => {
    this.drupalRelativeURL('/admin/modules')
      // Filter module list to ensure that collapsable <details> elements are expanded.
      .updateValue(
        'form.system-modules [data-drupal-selector="edit-text"]',
        module,
      )
      .waitForElementVisible(
        `form.system-modules [name="modules[${module}][enable]"]`,
        10000,
      )
      .click(`form.system-modules [name="modules[${module}][enable]"]`)
<<<<<<< HEAD
      .submitForm('form.system-modules')
      // Wait for the checkbox for the module to be disabled as a sign that the
      // module has been enabled.
      .waitForElementPresent(
        `form.system-modules [name="modules[${module}][enable]"]:disabled`,
        10000,
      );
=======
      .submitForm('form.system-modules');
    if (force) {
      // Click `Continue` if applicable.
      this.waitForElementPresent(
        '#system-modules-confirm-form',
        10000,
        false,
        () => self.click('input[value=Continue]'),
      );
    }
    // Wait for the checkbox for the module to be disabled as a sign that the
    // module has been enabled.
    this.waitForElementPresent(
      `form.system-modules [name="modules[${module}][enable]"]:disabled`,
      10000,
    );
>>>>>>> upstream/11.x
  }).perform(() => {
    if (typeof callback === 'function') {
      callback.call(self);
    }
  });

  return this;
};
