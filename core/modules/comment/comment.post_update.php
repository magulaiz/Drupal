<?php

/**
 * @file
 * Post update functions for the comment module.
 */

use Drupal\comment\CommentTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Implements hook_removed_post_updates().
 */
function comment_removed_post_updates() {
  return [
    'comment_post_update_enable_comment_admin_view' => '9.0.0',
    'comment_post_update_add_ip_address_setting' => '9.0.0',
  ];
}

/**
 * Add button label configuration to comment types.
 */
function comment_post_update_add_button_labels($sandbox) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'comment_type', function (CommentTypeInterface $commentType) {
    $commentType
      ->setCommentSubmitButtonLabel((string) new TranslatableMarkup('Save'))
      ->setReplySubmitButtonLabel((string) new TranslatableMarkup('Save'));
    return TRUE;
  });
}
