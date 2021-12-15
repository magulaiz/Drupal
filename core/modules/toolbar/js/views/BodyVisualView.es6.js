/**
 * @file
 * A Backbone view for the body element.
 */

(function ($, Drupal) {
  Drupal.toolbar.BodyVisualView = class extends Drupal.DrupalView {
    /** @lends Drupal.toolbar.BodyVisualView# */
    /**
     * Adjusts the body element with the toolbar position and dimension changes.
     *
     * @constructs
     *
     * @augments Backbone.View
     */
    constructor(options) {
      super();
      this.model = options.model;
      this.setElement(options.el);
      this.addChangeListener(this.render, `activeTray`);
      this.addChangeListener(
        this.isToolbarFixed,
        `isViewportOverflowConstrained`,
      );
      this.addChangeListener(this.isToolbarFixed, `isFixed`);
    }
    // initialize() {
    //   this.listenTo(this.model, 'change:activeTray ', this.render);
    //   this.listenTo(
    //     this.model,
    //     'change:isFixed change:isViewportOverflowConstrained',
    //     this.isToolbarFixed,
    //   );
    // },

    isToolbarFixed() {
      console.log('is toolbarfixed is called');
      // When the toolbar is fixed, it will not scroll with page scrolling.
      const isViewportOverflowConstrained = this.model.get(
        'isViewportOverflowConstrained',
      );
      $('body').toggleClass(
        'toolbar-fixed',
        isViewportOverflowConstrained || this.model.get('isFixed'),
      );
    }

    /**
     * {@inheritdoc}
     */
    render() {
      console.log('render body is called');
      $('body')
        // Toggle the toolbar-tray-open class on the body element. The class is
        // applied when a toolbar tray is active. Padding might be applied to
        // the body element to prevent the tray from overlapping content.
        .toggleClass('toolbar-tray-open', !!this.model.get('activeTray'));
    }
  };
})(jQuery, Drupal);
