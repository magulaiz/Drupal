<?php

namespace Drupal\link\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Defines a protocol validation constraint for links to broken internal URLs.
 */
#[Constraint(
  id: 'LinkNotExistingInternal',
  label: new TranslatableMarkup('No broken internal links', [], ['context' => 'Validation'])
)]
class LinkNotExistingInternalConstraint extends SymfonyConstraint {

  /**
   * Message when the path doesn't exist.
   *
   * @var string
   */
  public string $notFoundMessage = "The path '@uri' doesn't exist.";

  /**
   * Message when a parameter is not valid.
   *
   * @var string
   */
  public string $invalidParameterMessage = "The path '@uri' has an invalid parameter.";

  /**
   * Message when the path is missing mandatory parameters.
   *
   * @var string
   */
  public string $missingParameterMessage = "The path '@uri' is missing a required parameter.";

}
