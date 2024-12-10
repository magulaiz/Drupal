<?php

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks if a value is a valid entity type.
 *
 * This differs from the `EntityBundleExists` constraint in that checks that the
 * validated value is an *entity* of a particular bundle.
 */
#[Constraint(
  id: 'Bundle',
  label: new TranslatableMarkup('Bundle', [], ['context' => 'Validation']),
  type: ['entity', 'entity_reference']
)]
class BundleConstraint extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'The entity must be of bundle %bundle.';

  /**
   * The bundle option.
   *
   * @var string|array
   */
  public $bundle;

  /**
   * Gets the bundle option as array.
   *
   * @return string[]
   */
  public function getBundleOption() {
    // Support passing the bundle as string, but force it to be an array.
    if (!is_array($this->bundle)) {
      @trigger_error("The Bundle's constraint's support for string values for its 'bundle' option is deprecated in drupal:11.1.0 and will trigger a PHP error from drupal:12.0.0. Pass in an array of valid string values instead. See https://www.drupal.org/node/3418350", E_USER_DEPRECATED);
      $this->bundle = [$this->bundle];
    }
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption(): ?string {
    return 'bundle';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions(): array {
    return ['bundle'];
  }

}
