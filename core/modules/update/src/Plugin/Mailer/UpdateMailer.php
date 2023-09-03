<?php

namespace Drupal\update\Plugin\Mailer;

use Drupal\Core\Site\Settings;
use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Mailer\MailerBase;
use Drupal\Core\Url;
use Drupal\update\UpdateMailerInterface;
use Drupal\update\UpdateManagerInterface;

/**
 * Defines the Mailer plug-in for update module.
 *
 * @Mailer(
 *   id = "update",
 *   sub_types = { "status_notify" = @Translation("Available updates") },
 *   config = {"subject", "body", "to"},
 * )
 */
class UpdateMailer extends MailerBase implements UpdateMailerInterface {

  /**
   * {@inheritdoc}
   */
  public function cronNotify() {
    $this->doSend('status_notify');
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(EmailInterface $email) {
    if ($config = $this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      // It automatically sends a separate email to each recipient using the
      // correct language.
      $email->setTo($config->get('update.settings')->get('notification.emails'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    $site_name = $this->getConfig(TRUE)->get('system.site')->get('name');
    $notification_threshold = $config->get('update.settings')->get('notification.threshold');
    $messages = $this->getMessages();
    if (!$messages) {
      throw new SkipMailException('No updates available.');
    }

    $email->setVariable('site_name', $site_name)
      ->setVariable('notification_threshold', $notification_threshold)
      ->setVariable('update_status', Url::fromRoute('update.status')->toString())
      ->setVariable('update_settings', Url::fromRoute('update.settings')->toString())
      ->setVariable('messages', $messages);

    if (Settings::get('allow_authorize_operations', TRUE)) {
      $email->setVariable('update_manager', Url::fromRoute('update.report_update')->toString());
    }

    if ($this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $email->setSubject('New release(s) available for {{ site_name }}');
      // The email body is defined by template file
      // email__update__status_notify.html.twig.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSend(EmailInterface $email) {
    if (!$email->getError()) {
      \Drupal::state()->set('update.last_email_notification', $request_time);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function legacyMail(EmailInterface $email, array &$message) {
    // Conversion to the legacy mailer API can be done entirely automatically
    // except we need to add the parameters.
    $message['params'] = $this->getMessages(TRUE);
  }

  /**
   * Gets the update messages.
   *
   * @param bool $legacy
   *   (optional) TRUE to return legacy format
   *
   * @return array
   *   Array of message strings. In legacy format instead returns an array with
   *   key equals the report type and value equals the status reason code.
   */
  protected function getMessages(bool $legacy = FALSE) {
    $update_config = $this->getConfig(TRUE)->get('update.settings');
    $this->moduleHandler->loadInclude('update', 'install');
    $requirements = update_requirements('runtime');
    $messages = $params = [];
    $notify_all = ($update_config->get('notification.threshold') == 'all');

    foreach (['core', 'contrib'] as $report_type) {
      $status = $requirements["update_$report_type"];
      if (isset($status['severity']) && ($status['severity'] == REQUIREMENT_ERROR || ($notify_all && $status['reason'] == UpdateManagerInterface::NOT_CURRENT))) {
        $params[$report_type] = $status['reason'];
        $messages[] = _update_message_text($report_type, $status['reason']);
      }
    }

    return $legacy ? $params : $messages;
  }
}
