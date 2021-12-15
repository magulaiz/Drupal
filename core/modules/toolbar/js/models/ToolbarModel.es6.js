/**
 * @file
 * A Backbone Model for the toolbar.
 */

(function (Drupal) {
  /**
   * Backbone model for the toolbar.
   *
   * @constructor
   *
   * @augments Backbone.Model
   */
  Drupal.toolbar.ToolbarModel = class extends Drupal.DrupalModel {
    constructor(options) {
      super();
      this.activeTab = null;
      /**
       * Represents whether a tray is open or not. Stored as an ID selector e.g.
       * '#toolbar-item--1-tray'.
       *
       * @type {string}
       */
      this._activeTray = null;
      this.previousActiveTray = null;

      /**
       * Indicates whether the toolbar is displayed in an oriented fashion,
       * either horizontal or vertical.
       *
       * @type {bool}
       */
      this.isOriented = false;

      /**
       * Indicates whether the toolbar is positioned absolute (false) or fixed
       * (true).
       *
       * @type {bool}
       */
      this.isFixed = false;

      /**
       * Menu subtrees are loaded through an AJAX request only when the Toolbar
       * is set to a vertical orientation.
       *
       * @type {bool}
       */
      this.areSubtreesLoaded = false;

      /**
       * If the viewport overflow becomes constrained, isFixed must be true so
       * that elements in the trays aren't lost off-screen and impossible to
       * get to.
       *
       * @type {bool}
       */
      this.isViewportOverflowConstrained = false;

      /**
       * The orientation of the active tray.
       *
       * @type {string}
       */
      this.orientation = 'horizontal';

      /**
       * A tray is locked if a user toggled it to vertical. Otherwise a tray
       * will switch between vertical and horizontal orientation based on the
       * configured breakpoints. The locked state will be maintained across page
       * loads.
       *
       * @type {bool}
       */
      this.locked = false;

      /**
       * Indicates whether the tray orientation toggle is visible.
       *
       * @type {bool}
       */
      this.isTrayToggleVisible = true;

      /**
       * The height of the toolbar.
       *
       * @type {number}
       */
      this.height = null;

      /**
       * The current viewport offsets determined by {@link Drupal.displace}. The
       * offsets suggest how a module might position is components relative to
       * the viewport.
       *
       * @type {object}
       *
       * @prop {number} top
       * @prop {number} right
       * @prop {number} bottom
       * @prop {number} left
       */
      this.offsets = {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0,
      };
      Object.keys(options).forEach((key) => {
        if (this[key] && options[key] !== this[key]) {
          this.set(key, options.key);
        }
      });
      console.log('this Toolbarmodel', this);
    }
    set activeTray(value) {
      if (value !== this._activeTray) {
        this.previousActiveTray = this._activeTray;
      }
      this._activeTray = value;
    }
    get activeTray() {
      return this._activeTray;
    }
  };
})(Drupal);
