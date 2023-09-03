<?php

namespace Drupal\contact\Plugin\Mailer;

use Drupal\contact\ContactMailerInterface;
use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Mailer\MailerBase;
use Drupal\Core\Url;

/**
 * Defines a base class for contact module email builders.
 */
class ContactMailerBase extends MailerBase implements ContactMailerInterface {

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    /** @var \Drupal\user\UserInterface $sender */
    $sender = $email->getParam('sender');
    $message = $email->getParam('contact_message');

    $email->setVariableFromEntity('message', $message, 'mail')
      ->setVariable('subject', $message->getSubject())
      ->setVariable('site_name', $this->getConfig(TRUE)->get('system.site')->get('name'))
      ->setVariable('sender_name', $sender->getDisplayName())
      ->setVariable('sender_url', $sender->isAuthenticated() ? $sender->toUrl('canonical')->toString() : Url::fromUri('mailto:' . $sender->getEmail()));

    if ($email->getSubType() == 'mail') {
      $email->setReplyTo($sender);
    }
    else {
      // Send to the user. This automatically sets the correct language.
      $email->setTo($sender);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function legacyMail(EmailInterface $email, array &$message) {
    // Conversion to the legacy mailer API can be done entirely automatically
    // except we need to switch back to the legacy module and key.
    $prefix = substr($message['module'], strlen('contact_'));
    $message['module'] = 'contact';
    $message['key'] = $prefix . '_' . $message['key'];
  }

  /**
   * Performs sending function common to personal and page emails.
   *
   * @param array $params
   *   Array of parameters for email creation. If the sender is anonymous, it
   *   will be modified to contain the name and mail from the message.
   */
  protected function sendCommon(array &$params) {
    if (!$params['sender']->id()) {
      // Clone the sender, as we make changes to mail and name properties.
      $sender = clone $this->userStorage->load(0);

      // At this point, $sender contains an anonymous user, so we need to take
      // over the submitted form values. Clarify that the sender name is not
      // verified; it could potentially clash with a username on this site.
      $message = $email->getParam('contact_message');
      $sender->mail = $message->getSenderMail();
      $sender->name = $this->t('@name (not verified)', ['@name' => $message->getSenderName()]);
      $params['sender'] = $sender;
    }

    $this->doSend('mail', $params);

    if ($message->copySender()) {
      // Send sender copy email.
      $this->doSend('copy', $params);
    }
  }

}
