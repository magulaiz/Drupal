/**
 * @file
 * Splitbutton initialization.
 */

((Drupal, once) => {
  /**
   * Process elements with the [data-drupal-splitbutton-multiple] attribute.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches splitbutton behaviors.
   */

  Drupal.behaviors.Splitbutton = {
    attach(context) {
      const splitbuttons = once(
        'splitbutton',
        '[data-drupal-splitbutton-multiple]',
        context,
      );
      splitbuttons.map((splitbutton) =>
        Drupal.splitbuttons.push(new Drupal.Splitbutton(splitbutton)),
      );
    },
  };

  Drupal.splitbuttons = [];
})(Drupal, once);
