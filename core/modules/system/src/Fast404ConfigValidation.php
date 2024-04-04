<?php

namespace Drupal\system;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Provides validation callback.
 */
class Fast404ConfigValidation {

  /**
   * Validates an HTML.
   *
   * @param string $html
   *   The HTML to validate.
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   *   The validation execution context.
   */
  public static function validatehtml($html, ExecutionContextInterface $context) {
    $dom = new \DOMDocument();
    // Suppress warnings caused by invalid HTML.
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML($html);
    // Retrieve the errors and clear them.
    $errors = libxml_get_errors();
    libxml_clear_errors();
    if (!empty($errors)) {
      $context->addViolation('HTML is not valid');
    }
  }

}
