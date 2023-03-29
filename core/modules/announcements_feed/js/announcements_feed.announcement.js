(function (Drupal, once) {
  /**
   * Update the announce icon when tray is opened.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.announce = {
    attach(context) {
      const announcement = once(
        'announce-new',
        '.announce-new',
        context,
      ).shift();
      if (announcement) {
        announcement.addEventListener('click', function (e) {
          e.currentTarget.classList.remove('announce-new');
        });
      }
    },
  };
})(Drupal, once);
