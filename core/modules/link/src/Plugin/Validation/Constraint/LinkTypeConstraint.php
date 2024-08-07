<?php

namespace Drupal\link\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Validation constraint for links receiving data allowed by its settings.
 */
#[Constraint(
  id: 'LinkType',
  label: new TranslatableMarkup('Link data valid for link type.', [], ['context' => 'Validation'])
)]
class LinkTypeConstraint extends SymfonyConstraint {

  /**
   * Default message.
   *
   * @var string
   */
  public string $invalidMessage = "The path '@uri' is invalid.";

  /**
   * Message when path is external on internal only field.
   *
   * @var string
   */
  public string $onlyInternalMessage = "The path '@uri' is external, but the @field-label field only supports internal paths.";

  /**
   * Message when path is internal on external only field.
   *
   * @var string
   */
  public string $onlyExternalMessage = "The path '@uri' is internal, but the @field-label field only supports external paths.";

}
