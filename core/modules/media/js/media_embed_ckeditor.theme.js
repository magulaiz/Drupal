/**
 * @file
 * Theme elements for the Media Embed text editor plugins.
 */

<<<<<<< HEAD
(function (Drupal) {
  Drupal.theme.mediaEmbedPreviewError = function () {
    return "<div>".concat(Drupal.t('An error occurred while trying to preview the media. Please save your work and reload this page.'), "</div>");
  };
})(Drupal);
=======
((Drupal) => {
  /**
   * Themes the error displayed when the media embed preview fails.
   *
   * @return {string}
   *   A string representing a DOM fragment.
   *
   * @see media-embed-error.html.twig
   */
  Drupal.theme.mediaEmbedPreviewError = () =>
    `<div>${Drupal.t(
      'An error occurred while trying to preview the media. Save your work and reload this page.',
    )}</div>`;
})(Drupal);
>>>>>>> upstream/11.x
