<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

#[Constraint(
  id: 'LangcodeRequiredIfTranslatableValues',
  label: new TranslatableMarkup('Translatable config has langcode', [], ['context' => 'Validation']),
  type: ['config_object']
)]
class LangcodeRequiredIfTranslatableValuesConstraint extends SymfonyConstraint {

  /**
   * The error message if this config object is missing a `langcode`.
   *
   * @var string
   */
  public string $missingMessage = "The @name config object contains translatable values and must hence specify a 'langcode' key.";

  /**
   * The error message if this config object contains a superfluous `langcode`.
   *
   * @var string
   */
  public string $superfluousMessage = "The @name config object does not contain translatable values and should hence not specify a 'langcode' key.";

}
