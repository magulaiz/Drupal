<?php

namespace Drupal\link\Plugin\Validation\Constraint;

use Drupal\link\LinkItemInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for links receiving data allowed by its settings.
 */
class LinkTypeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    if (!$value instanceof LinkItemInterface) {
      return;
    }

    // Try to resolve the given URI to a URL. It may fail if it's schemeless.
    try {
      $url = $value->getUrl();
      // If the link field doesn't support both internal and external links,
      // check whether the URL (a resolved URI) is in fact violating either
      // restriction.
      $link_type = $value->getFieldDefinition()->getSetting('link_type');
      if ($url->isExternal() && !($link_type & LinkItemInterface::LINK_EXTERNAL)) {
        $this->context->addViolation($constraint->onlyInternalMessage, ['@uri' => $value->uri]);
      }
      elseif (!$url->isExternal() && !($link_type & LinkItemInterface::LINK_INTERNAL)) {
        $this->context->addViolation($constraint->onlyExternalMessage, ['@uri' => $value->uri]);
      }
    }
    catch (\InvalidArgumentException $e) {
      $this->context->addViolation($constraint->invalidMessage, ['@uri' => $value->uri]);
    }
  }

}
