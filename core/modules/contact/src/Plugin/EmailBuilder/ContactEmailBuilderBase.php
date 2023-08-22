<?php

namespace Drupal\contact\Plugin\EmailBuilder;

use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Mailer\EmailBuilderBase;
use Drupal\Core\Url;

/**
 * Defines a base class for contact module email builders.
 */
class ContactEmailBuilderBase extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(EmailInterface $email) {
    if ($email->getSubType() == 'mail' && $email->getParam('contact_message')->copySender()) {
      // Send an extra sender copy email using the same type/params and a
      // different sub-type.
      $email->clone('copy');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    /** @var \Drupal\user\UserInterface $sender */
    $sender = $email->getParam('sender');
    $contact_message = $email->getParam('contact_message');

    $email->setVariableFromEntity('message', $contact_message, 'mail')
      ->setVariable('subject', $contact_message->getSubject())
      ->setVariable('site_name', $this->helper()->config()->get('system.site')->get('name'))
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

}
