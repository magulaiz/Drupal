/**
 * @file
 * Model for the toolbar.
 */

(function (Drupal) {
  /**
   * Model for the toolbar.
   *
   * @constructor
   *
   * @augments Drupal.DrupalModel
   */
  Drupal.toolbar.ToolbarModel = class extends Drupal.DrupalModel {
    constructor(values, options) {
      super(values, options);
    }

    get orientation() {
      console.log('get orientation');
      return this._orientation;
    }

    set orientation(value) {
      console.log('set orientation');

      // Prevent the orientation being set to horizontal if it is locked, unless
      // lockedOverride was set to TRUE.
      if (
        value === 'horizontal' &&
        this.get('locked') &&
        !this.lockedOverride
      ) {
        console.log('BLOCKED ORIENTATION CHANGE');
        return;
      }
      console.log('allow orientation change', this);
      this._orientation = value;

      // Return lockedOverride to the default value of false immediately after
      // updating orientation.
      this.lockedOverride = false;
    }
  };
  Drupal.toolbar.ToolbarModel.prototype.defaults = {
    /**
     * The active toolbar tab. All other tabs should be inactive under
     * normal circumstances. It will remain active across page loads. The
     * active item is stored as an ID selector e.g. '#toolbar-item--1'.
     *
     * @type {string}
     */
    activeTab: null,

    /**
     * Represents whether a tray is open or not. Stored as an ID selector e.g.
     * '#toolbar-item--1-tray'.
     *
     * @type {string}
     */
    activeTray: null,

    /**
     * Indicates whether the toolbar is displayed in an oriented fashion,
     * either horizontal or vertical.
     *
     * @type {bool}
     */
    isOriented: false,

    /**
     * Indicates whether the toolbar is positioned absolute (false) or fixed
     * (true).
     *
     * @type {bool}
     */
    isFixed: false,

    /**
     * Menu subtrees are loaded through an AJAX request only when the Toolbar
     * is set to a vertical orientation.
     *
     * @type {bool}
     */
    areSubtreesLoaded: false,

    /**
     * If the viewport overflow becomes constrained, isFixed must be true so
     * that elements in the trays aren't lost off-screen and impossible to
     * get to.
     *
     * @type {bool}
     */
    isViewportOverflowConstrained: false,

    /**
     * A tray is locked if a user toggled it to vertical. Otherwise a tray
     * will switch between vertical and horizontal orientation based on the
     * configured breakpoints. The locked state will be maintained across page
     * loads.
     *
     * @type {bool}
     */
    locked: false,

    /**
     * Set to true to allow orientation changes on a locked tray.
     *
     * The value will switch back to false after the orientation changes.
     *
     * @type {boolean}
     */
    lockedOverride: false,

    /**
     * Indicates whether the tray orientation toggle is visible.
     *
     * @type {bool}
     */
    isTrayToggleVisible: true,

    /**
     * The height of the toolbar.
     *
     * @type {number}
     */
    height: null,

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
    offsets: {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
    },

    /**
     * The orientation of the active tray.
     *
     * Made public as `orientation` via dedicated get and set functions .
     *
     * @type {string}
     */
    _orientation: 'horizontal',
  };
})(Drupal);
