<?php

namespace Drupal\contact\Plugin\EmailBuilder;

use Drupal\Core\Url;
use Drupal\Core\Mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for contact module page forms.
 *
 * There are separate email builders for personal and page forms, as they have
 * differences in the ID, parameters and variables.
 *
 * Emails for page forms are associated with a contact_form entity. The
 * contact form entity is added as the 3rd part of the email ID, so that
 * different contact forms can have different configuration.
 *
 * @EmailBuilder(
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
class ContactPageEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(EmailInterface $email) {
    if ($email->getSubType() == 'mail') {
      /** @var Drupal\contact\ContactFormInterface $contact_form */
      $contact_form = $email->getEntity();

      // Send an extra auto-reply email using the same type/params and a
      // different sub-type. This email will skip sending if there is no
      // configured reply.
      $email->clone('autoreply');

      if ($this->doConfig()) {
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

    if ($this->doConfig()) {
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
