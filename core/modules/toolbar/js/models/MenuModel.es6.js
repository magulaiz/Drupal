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
   * @augments Drupal.Model
   */
  Drupal.toolbar.MenuModel = class extends Drupal.DrupalModel {
    constructor() {
      super();
      this.subtrees = {};
    }
  };
})(Drupal);
