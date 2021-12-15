/**
 * @file
 * A Backbone view for the collapsible menus.
 */

(function ($, Drupal) {
  Drupal.toolbar.MenuVisualView = class extends Drupal.DrupalView {
    /** @lends Drupal.toolbar.MenuVisualView# */
    /**
     * Backbone View for collapsible menus.
     *
     * @constructs
     *
     * @augments Backbone.View
     */
    constructor(options) {
      super();
      this.model = options.model;
      this.setElement(options.el);
      this.addChangeListener(this.render, `subtrees`);
    }
    // initialize() {
    //   this.listenTo(this.model, 'change:subtrees', this.render);
    // }

    /**
     * {@inheritdoc}
     */
    render() {
      console.log('render in menuvisualview is called');
      const subtrees = this.model.get('subtrees');
      // Add subtrees.
      Object.keys(subtrees || {}).forEach((id) => {
        $(once('toolbar-subtrees', $(this.el).find(`#toolbar-link-${id}`))).after(
          subtrees[id],
        );
      });
      // Render the main menu as a nested, collapsible accordion.
      if ('drupalToolbarMenu' in $.fn) {
        $(this.el).children('.toolbar-menu').drupalToolbarMenu();
      }
    }
  };
})(jQuery, Drupal);
