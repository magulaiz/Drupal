<?php

namespace Drupal\contact\Plugin\EmailBuilder;

use Drupal\Core\Mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for contact module personal forms.
 *
 * There are separate email builders for personal and page forms, as they have
 * differences in the ID, parameters and variables.
 *
 * @EmailBuilder(
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
class ContactUserEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    parent::build($email);

    /** @var \Drupal\user\UserInterface $recipient */
    $recipient = $email->getParam('recipient');
    $email->setVariable('recipient_name', $recipient->getDisplayName())
      ->setVariable('recipient_edit_url', $recipient->toUrl('edit-form')->toString());

    if ($email->getSubType() == 'mail') {
      // Send to the user. This automatically sets the correct language.
      $email->setTo($recipient);
    }

    if ($this->doConfig()) {
      // This part will be replaced by a generic configurable email mechanism.
      $email->setSubject('[{{ site_name }}] {{ subject }}');
    }

    // The email body is defined by template files
    // email__contact__user__*.html.twig.
  }

}
