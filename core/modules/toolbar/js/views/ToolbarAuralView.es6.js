/**
 * @file
 * A Backbone view for the aural feedback of the toolbar.
 */

(function (Backbone, Drupal) {
  Drupal.toolbar.ToolbarAuralView = class extends Drupal.DrupalView {
    /**
     * Backbone view for the aural feedback of the toolbar.
     *
     * @constructs
     *
     * @augments Backbone.View
     *
     * @param {object} options
     *   Options for the view.
     * @param {object} options.strings
     *   Various strings to use in the view.
     */
    constructor(options) {
      super();
      this.model = options.model;
      this.strings = options.strings;
      this.el = options.el;
      this.addChangeListener(this.onOrientationChange, `orientation`);
      this.addChangeListener(this.onActiveTrayChange, `activeTray`);
    }

    // initialize(options) {
    //   this.strings = options.strings;
    //
    //   this.listenTo(
    //     this.model,
    //     'change:orientation',
    //     this.onOrientationChange,
    //   );
    //   this.listenTo(this.model, 'change:activeTray', this.onActiveTrayChange);
    // }

    /**
     * Announces an orientation change.
     *
     * @param {Drupal.toolbar.ToolbarModel} model
     *   The toolbar model in question.
     * @param {string} orientation
     *   The new value of the orientation attribute in the model.
     */
    onOrientationChange() {
      console.log('onOrientationChange function');
      Drupal.announce(
        Drupal.t('Tray orientation changed to @orientation.', {
          '@orientation': this.model.orientation,
        }),
      );
    }

    /**
     * Announces a changed active tray.
     *
     * @param {Drupal.toolbar.ToolbarModel} model
     *   The toolbar model in question.
     * @param {HTMLElement} tray
     *   The new value of the tray attribute in the model.
     */
    onActiveTrayChange() {
      // debugger;
      const tray = this.model.get('activeTray');
      const relevantTray =
        // TODO: .previous replacement?
        tray === null ? this.model.get('previousActiveTray') : tray;
      // Current activeTray and previous activeTray are empty, no state change
      // to announce.
      if (!relevantTray) {
        return;
      }
      const action = tray === null ? Drupal.t('closed') : Drupal.t('opened');
      const trayNameElement = relevantTray.querySelector('.toolbar-tray-name');
      let text;
      if (trayNameElement !== null) {
        text = Drupal.t('Tray "@tray" @action.', {
          '@tray': trayNameElement.textContent,
          '@action': action,
        });
      } else {
        text = Drupal.t('Tray @action.', { '@action': action });
      }
      Drupal.announce(text);
    }
  };
})(Backbone, Drupal);
