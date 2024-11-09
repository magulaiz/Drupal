/**
 * Install the given module(s).
 *
 * @param {string|array} modules
 *   The module machine name(s) to enable.
 * @param {boolean} force
 *   Force to install dependencies if applicable.
 * @param {function} callback
 *   A callback which will be called, when the module(s) have been enabled.
 * @return {object}
 *   The drupalInstallModule command.
 */
exports.command = function drupalInstallModule(modules, force, callback) {
  const self = this;
  modules = [].concat(modules);
  this.drupalLoginAsAdmin(() => {
    this.drupalRelativeURL('/admin/modules');
    modules.forEach((module) => {
      // Filter module list to ensure that collapsable <details> elements are expanded.
      this.updateValue(
        'form.system-modules [data-drupal-selector="edit-text"]',
        module,
      )
        .waitForElementVisible(
          `form.system-modules [name="modules[${module}][enable]"]`,
          10000,
        )
        .click(`form.system-modules [name="modules[${module}][enable]"]`);
    });
    this.submitForm('form.system-modules');
    if (force) {
      // Click `Continue` if applicable.
      this.waitForElementPresent(
        '#system-modules-confirm-form, #system-modules-non-stable-confirm-form',
        10000,
        false,
        () => self.click('input[value=Continue]'),
      );
    }
    modules.forEach((module) => {
      // Wait for the checkbox for the module to be disabled as a sign that the
      // module has been enabled.
      this.waitForElementPresent(
        `form.system-modules [name="modules[${module}][enable]"]:disabled`,
        10000,
      );
    });
  }).perform(() => {
    if (typeof callback === 'function') {
      callback.call(self);
    }
  });

  return this;
};
