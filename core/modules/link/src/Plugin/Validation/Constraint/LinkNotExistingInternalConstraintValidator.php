<?php

namespace Drupal\link\Plugin\Validation\Constraint;

use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LinkNotExistingInternal constraint.
 */
class LinkNotExistingInternalConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    if (isset($value)) {
      try {
        /** @var \Drupal\Core\Url $url */
        $url = $value->getUrl();
      }
      // If the URL is malformed this constraint cannot check further.
      catch (\InvalidArgumentException) {
        return;
      }

      if ($url->isRouted()) {
        try {
          $url->toString(TRUE);
        }
        // The following exceptions are all possible during URL generation, and
        // should be considered as disallowed URLs.
        catch (RouteNotFoundException) {
          $this->context->addViolation($constraint->notFoundMessage, ['@uri' => $value->uri]);
        }
        catch (InvalidParameterException) {
          $this->context->addViolation($constraint->invalidParameterMessage, ['@uri' => $value->uri]);
        }
        catch (MissingMandatoryParametersException) {
          $this->context->addViolation($constraint->missingParameterMessage, ['@uri' => $value->uri]);
        }
      }
    }
  }

}
