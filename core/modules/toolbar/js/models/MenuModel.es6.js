/**
 * @file
 * A Backbone Model for collapsible menus.
 */

(function (Drupal) {
  /**
   * Model for collapsible menus.
   *
   * @constructor
   *
   * @augments Drupal.DrupalModel
   */
  Drupal.toolbar._MenuModel = class extends (
    Drupal.DrupalModel.extend({
      defaults: {
        subtrees: {},
      },
    })
  ) {};

  Drupal.toolbar.MenuModel = new Proxy(Drupal.toolbar._MenuModel, {
    construct(target, args) {
      Drupal.deprecationError({
        message:
          'Drupal.toolbar.MenuModel will be marked as internal in drupal:10.0.0.',
      });
      return new target(...args);
    },
  });
})(Drupal);
