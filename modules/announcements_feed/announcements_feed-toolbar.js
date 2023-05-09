/**
 * @file
 * Behavior for Announce toolbar.
 */

(function ($) {
  'use strict';

  /**
  * Attach the access by user behavior which initiates ajax.
  *
  * @type {Drupal~behavior}
  */
  Drupal.behaviors.newAnnouncement = {
    attach: function (context, settings) {
      if ($('#toolbar-link-admin-announcement').hasClass('announce-new')) {
        $('#toolbar-link-admin-announcement', context).click(function () {
          $('#toolbar-link-admin-announcement').removeClass('announce-new');
        });
      }
    }
  };
})(jQuery);
