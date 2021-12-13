/**
 * @file
 * A Backbone Model for collapsible menus.
 */

(function (Backbone, Drupal) {
  /**
   * Backbone Model for collapsible menus.
   *
   * @constructor
   *
   * @augments Backbone.Model
   */
  Drupal.toolbar.MenuModel = class extends Drupal.DrupalModel {
    constructor() {
      super();
      console.log('i am the constructor for menumodel.', this);
      this.subtrees = {};
    }
  };
})(Backbone, Drupal);
