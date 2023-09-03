<?php

namespace Drupal\user\Plugin\Mailer;

use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Mailer\MailerBase;
use Drupal\user\UserInterface;
use Drupal\user\UserMailerInterface;

/**
 * Defines the Mailer plug-in for user module.
 *
 * @Mailer(
 *   id = "user",
 *   sub_types = {
 *     "cancel_confirm" = @Translation("Account cancellation confirmation"),
 *     "password_reset" = @Translation("Password recovery"),
 *     "register_admin_created" = @Translation("Account created by administrator"),
 *     "register_no_approval_required" = @Translation("Registration confirmation (No approval required)"),
 *     "register_pending_approval" = @Translation("Registration confirmation (Pending approval)"),
 *     "register_pending_approval_admin" = @Translation("Admin (user awaiting approval)"),
 *     "status_activated" = @Translation("Account activation"),
 *     "status_blocked" = @Translation("Account blocked"),
 *     "status_canceled" = @Translation("Account cancelled"),
 *   },
 *   config = {"subject", "body", "skip_sending"},
 * )
 */
class UserMailer extends MailerBase implements UserMailerInterface {

  /**
   * {@inheritdoc}
   */
  public function notify(string $op, UserInterface $user) {
    $params = ['user' => $user];
    $ok = $this->doSend($op, $params);

    if ($op == 'register_pending_approval') {
      // Send an extra email to the admin using the same type/params and a
      // different sub-type.
      $this->doSend('register_pending_approval_admin', $params);
    }

    return $ok;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(EmailInterface $email) {
    $op = $email->getSubType();

    if ($op != 'register_pending_approval_admin') {
      // Send to the user. This automatically sets the correct language.
      $email->setTo($email->getParam('user'));
    }

    if ($config = $this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $site_settings = $config->get('system.site');
      $site_mail = $site_settings->get('mail_notification') ?: $site_settings->get('mail') ?: ini_get('sendmail_from');

      if ($op == 'register_pending_approval_admin') {
        // This automatically sets the correct language.
        $email->setTo($site_mail);
      }
      else {
        $email->setReplyTo($site_mail);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    $email->replaceTokens(['callback' => 'user_mail_tokens', 'clear' => TRUE]);

    if ($config = $this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $op = $email->getSubType();
      if (!$config->get('user.settings')->get("notify.$op")) {
        throw new SkipMailException('Notification disabled in settings.');
      }
      $mail_config = $config->get('user.mail');
      $email->setSubject($mail_config->get("$op.subject"));
      // Format an HTML body based on the configured unformatted text. This
      // uses the default text format however module code can override.
      $email->formatBody($mail_config->get("$op.body"));
    }
  }

}
