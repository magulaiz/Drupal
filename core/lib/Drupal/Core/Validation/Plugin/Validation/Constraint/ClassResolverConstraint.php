<?php

declare(strict_types=1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks if a callback method on a service returns true.
 *
 *  For example to call the method 'isValidScheme' on the service
 *  'stream_wrapper_manager', use: ['stream_wrapper_manager', 'isValidScheme'].
 *  This method should return TRUE when the result is valid. All other values
 *  will be considered as invalid.
 */
#[Constraint(
  id: 'ClassResolver',
  label: new TranslatableMarkup('Call a method on a service', [], ['context' => 'Validation']),
  type: FALSE,
)]
class ClassResolverConstraint extends SymfonyConstraint {

  /**
   * The error message if validation fails.
   *
   * @var string
   */
  public string $message = "Calling '@callback` method with value '@value' on '@classOrService' evaluated as invalid.";

  /**
   * Class or service.
   *
   * @var array
   */
  public string $classOrService;


  /**
   * Method to call.
   *
   * @var string
   */
  public string $method;

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions(): array {
    return ['classOrService', 'method'];
  }

}
