/**
 * @file
 * Statistics functionality.
 */

(function ($, drupalSettings) {
  setTimeout(async () => {
    await fetch(drupalSettings.statistics.url, {
      method: 'POST',
      cache: 'no-cache',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: drupalSettings.statistics.data,
    });
  });
})(jQuery, drupalSettings);
