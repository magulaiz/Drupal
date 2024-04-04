<?php

declare(strict_types=1);

namespace Drupal\book\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

class BookFormCallbacks {

  /**
   * Form submission handler for node_form().
   *
   * This handler is run when JavaScript is disabled. It triggers the form to
   * rebuild so that the "Parent item" options are changed to reflect the newly
   * selected book. When JavaScript is enabled, the submit button that triggers
   * this handler is hidden, and the "Book" dropdown directly triggers the
   * book_form_update() Ajax callback instead.
   *
   * @see book_form_update()
   * @see book_form_node_form_alter()
   */
  #[TrustedCallback]
  public static function pickBookNoJsSubmit(array $form, FormStateInterface $form_state) {
    $node = $form_state->getFormObject()->getEntity();
    $node->book = $form_state->getValue('book');
    $form_state->setRebuild();
  }

}
