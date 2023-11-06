<?php

namespace Drupal\user;

/**
 * Defines the Mailer interface for user module.
 */
interface UserMailerInterface {

  /**
   * Sends a notification email following a change to a user account.
   *
   * @param string $op
   *   The operation being performed on the account. Possible values:
   *   - 'register_admin_created': Welcome message for user created by the admin.
   *   - 'register_no_approval_required': Welcome message when user
   *     self-registers.
   *   - 'register_pending_approval': Welcome message, user pending admin
   *     approval.
   *   - 'password_reset': Password recovery request.
   *   - 'status_activated': Account activated.
   *   - 'status_blocked': Account blocked.
   *   - 'cancel_confirm': Account cancellation request.
   *   - 'status_canceled': Account canceled.
   * @param \Drupal\user\UserInterface $user
   *   The user object of the account being notified.
   *
   * @return bool
   *   Whether successful.
   */
  public function notify(string $op, UserInterface $user);

}
