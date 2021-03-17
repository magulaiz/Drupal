<?php

/**
 * @file
 * Post update functions for User module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\user\Entity\Role;

/**
 * Implements hook_removed_post_updates().
 */
function user_removed_post_updates() {
  return [
    'user_post_update_enforce_order_of_permissions' => '9.0.0',
  ];
}

/**
 * Calculate role dependencies and remove non-existent permissions.
 */
function user_post_update_update_roles(&$sandbox = NULL) {
  $cleaned_roles = [];
  $existing_permissions = array_keys(\Drupal::service('user.permissions')->getPermissions());
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (Role $role) use ($existing_permissions, &$cleaned_roles) {
    $removed_permissions = array_diff($role->getPermissions(), $existing_permissions);
    if (!empty($removed_permissions)) {
      $cleaned_roles[] = $role->label();
      \Drupal::logger('update')->notice(
        'The role %role has had the following non-existent permission(s) removed: %permissions.',
        ['%role' => $role->label(), '%permissions' => implode(', ', $removed_permissions)]
      );
    }
    $permissions = array_intersect($role->getPermissions(), $existing_permissions);
    $role->set('permissions', $permissions);
    return TRUE;
  });

  if (!empty($cleaned_roles)) {
    return new PluralTranslatableMarkup(
      count($cleaned_roles),
      'The role %role_list has had non-existent permissions removed. Check the logs for details.',
      'The roles %role_list have had non-existent permissions removed. Check the logs for details.',
      ['%role_list' => implode(', ', $cleaned_roles)]
    );
  }

/**
 * Update config for change mail notifications.
 */
function user_post_update_mail_change() {
  $config_factory = \Drupal::service('config.factory');

  $config_factory->getEditable('user.settings')
    ->set('notify.mail_change_notification', FALSE)
    ->set('notify.mail_change_verification', FALSE)
    ->set('mail_change_timeout', 86400)
    ->save();

  $mail_change_notification = [
    'body' => "[user:display-name],\n\nA request to change your email address has been made at [site:name]. In order to complete the change you will need to follow the instructions sent to your new email address within one day.\n\nIf you did not intend to make this change, contact [site-email].",
    'subject' => 'Email change for [user:display-name] at [site:name]',
  ];
  $mail_change_verification = [
    'body' => "[user:display-name],\n\nA request to change your email address has been made at [site:name]. You need to verify the change by clicking on the link below or copying and pasting it in your browser:\n\n[user:mail-change-url]\n\nThis is a one-time URL, so it can be used only once. It expires after one day. If not used, your email address at [site:name] will not change.",
    'subject' => 'Email change for [user:display-name] at [site:name]',
  ];

  $config_factory->getEditable('user.mail')
    ->set('mail_change_notification', $mail_change_notification)
    ->set('mail_change_verification', $mail_change_verification)
    ->save();
}
