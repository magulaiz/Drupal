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
  Drupal.toolbar.MenuModel = class extends Drupal.DrupalModel {}
  Drupal.toolbar.MenuModel.prototype.defaults = {
    subtrees: {}
  }
})(Drupal);
