<?php

namespace Drupal\contact\Plugin\Mailer;

use Drupal\contact\Entity\ContactForm;
use Drupal\contact\MessageInterface;
use Drupal\Core\Mailer\EmailInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Defines the Mailer plug-in for contact module page forms.
 *
 * There are separate mailers for personal and page forms, as they have
 * differences in the ID, parameters and variables.
 *
 * Emails for page forms are associated with a contact_form entity. The
 * contact form entity is added as the 3rd part of the email ID, so that
 * different contact forms can have different configuration.
 *
 * @Mailer(
 *   id = "contact__page",
 *   label = @Translation("Contact page"),
 *   sub_types = {
 *     "mail" = @Translation("Message"),
 *     "copy" = @Translation("Sender copy"),
 *     "autoreply" = @Translation("Auto-reply"),
 *   },
 *   entity = "contact_form",
 *   config = {"subject", "body", "to"},
 * )
 */
class ContactPageMailer extends ContactMailerBase {

  /**
   * {@inheritdoc}
   */
  public function sendMailMessages(MessageInterface $message, AccountInterface $sender) {
    $params = [
      'contact_message' => $message,
      'sender' => $sender,
      'contact_form' => $message->getContactForm(),
    ];
    $this->sendCommon($params);

    // Send auto-reply email. This email will skip sending if there is no
    // configured reply.
    $this->doSend('autoreply', $params);

    $this->logger->notice('%sender-name (@sender-from) sent an email regarding %contact_form.', [
      '%sender-name' => $params['sender']->getAccountName(),
      '@sender-from' => $params['sender']->getEmail() ?? '',
      '%contact_form' => $contact_form->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(EmailInterface $email) {
    if ($email->getSubType() == 'mail') {
      /** @var Drupal\contact\ContactFormInterface $contact_form */
      $contact_form = $email->getEntity();

      if ($this->getConfig()) {
        // This part will be replaced by a generic configurable email
        // mechanism. automatically sends a separate email to each recipient
        // using the correct language.
        $email->setTo(implode(', ', $contact_form->getRecipients()));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    parent::build($email);

    /** @var Drupal\contact\ContactFormInterface $contact_form */
    $contact_form = $email->getEntity();
    $email->setVariable('form', $contact_form->label())
      ->setVariable('form_url', Url::fromRoute('<current>')->toString());

    if ($this->getConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $email->setSubject('[{{ form }}] {{ subject }}');

      if ($email->getSubType() == 'autoreply') {
        if ($reply = $contact_form->getReply()) {
          // Format an HTML body based on the configured unformatted text. This
          // uses the default text format however module code can override.
          $email->formatBody($reply);
        }
        else {
          throw new SkipMailException('No reply configured.');
        }
      }
    }

    // For the other sub-types,  the email body is defined by template files
    // email__contact__page__*.html.twig.
  }

}
