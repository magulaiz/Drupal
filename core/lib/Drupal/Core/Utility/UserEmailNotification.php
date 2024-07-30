<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Drupal\Core\Utility;

/**
 * Enumeration of constant related User email notification.
 */
enum UserEmailNotification: string {
  /**
   * Email notification operation for the welcome message sent to the user
   * created by an administrator.
   */  
  case RegisterAdminCreated = 'register_admin_created';

  /**
   * Email notification operation for the welcome message sent to the user who
   * registered and didn't need to be approved by an administrator.
   */
  case RegisterNoApprovalRequired = 'register_no_approval_required';

  /**
   * Email notification operation for the welcome message sent to the user who
   * registered, but needs to be approved by an administrator.
   */
  case RegisterPendingApproval = 'register_pending_approval';

  /**
   * Email notification operation for the message sent to an administrator, after
   * a user has registered and needs approval.
   */
  case RegisterPendingApprovalAdmin = 'register_pending_approval_admin';

  /**
   * Email notification operation for the message sent to the user who requested
   * a password recovery.
   */
  case PasswordReset = 'password_reset';

  /**
   * Email notification operation for the message sent to the user whose status
   * has been activated.
   */
  case StatusActivated = 'status_activated';

  /**
   * Email notification operation for the message sent to the user whose status
   * has been set to blocked.
   */
  case StatusBlocked = 'status_blocked';

  /**
   * Email notification operation for the message sent to the user whose status
   * has been set to canceled.
   */
  case StatusCanceled = 'status_canceled';

  /**
   * Email notification operation for the message sent to the user to confirm
   * the account cancellation request.
   */
  case CancelConfirm = 'cancel_confirm';

}
