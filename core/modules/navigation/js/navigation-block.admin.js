/**
 * @file
 * Navigation block admin behaviors.
 */

((Drupal, once) => {
  /**
   * Highlights a navigation block.
   *
   * Highlights the navigation block that was just placed into the navigation
   * block listing.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the navigation block placement highlighting.
   */
  Drupal.behaviors.navigationBlockHighlightPlacement = {
    attach(context, settings) {
      // Ensure that the navigation block we are attempting to scroll to
      // actually exists.
      if (settings.navigationBlockPlacement) {
        once(
          'navigation-block-highlight',
          '.js-navigation-block-placed',
          context,
        ).forEach((container) => {
          container.scrollIntoView({ behavior: 'smooth' });
        });
      }
    },
  };
})(Drupal, once);
