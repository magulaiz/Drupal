<?php

namespace Drupal\contact\Plugin\Mailer;

use Drupal\contact\MessageInterface;
use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the Mailer plug-in for contact module personal forms.
 *
 * There are separate mailers for personal and page forms, as they have
 * differences in the ID, parameters and variables.
 *
 * @Mailer(
 *   id = "contact__user",
 *   label = @Translation("Personal contact form"),
 *   sub_types = {
 *     "mail" = @Translation("Message"),
 *     "copy" = @Translation("Sender copy"),
 *   },
 *   config = {"subject", "body"},
 * )
 *
 */
class ContactUserMailer extends ContactMailerBase {

  /**
   * {@inheritdoc}
   */
  public function sendMailMessages(MessageInterface $message, AccountInterface $sender) {
    $params = ['contact_message' => $message, 'sender' => $sender];
    $this->sendCommon($params);
    $this->logger->notice('%sender-name (@sender-from) sent %recipient-name an email.', [
      '%sender-name' => $params['sender']->getAccountName(),
      '@sender-from' => $params['sender']->getEmail(),
      '%recipient-name' => $message->getPersonalRecipient()->getAccountName(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    parent::build($email);

    /** @var \Drupal\user\UserInterface $recipient */
    $recipient = $email->getParam('contact_message')->getPersonalRecipient();
    $email->setVariable('recipient_name', $recipient->getDisplayName())
      ->setVariable('recipient_edit_url', $recipient->toUrl('edit-form')->toString());

    if ($email->getSubType() == 'mail') {
      // Send to the user. This automatically sets the correct language.
      $email->setTo($recipient);
    }

    if ($this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $email->setSubject('[{{ site_name }}] {{ subject }}');
    }

    // The email body is defined by template files
    // email__contact__user__*.html.twig.
  }

}
