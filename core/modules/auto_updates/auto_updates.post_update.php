<?php

/**
 * @file
 * Contains post-update hooks for Automatic Updates.
 */

declare(strict_types = 1);

use Drupal\auto_updates\StatusCheckMailer;

/**
 * Creates the auto_updates.settings:status_check_mail config.
 */
function auto_updates_post_update_create_status_check_mail_config(): void {
  \Drupal::configFactory()
    ->getEditable('auto_updates.settings')
    ->set('status_check_mail', StatusCheckMailer::ERRORS_ONLY)
    ->save();
}
