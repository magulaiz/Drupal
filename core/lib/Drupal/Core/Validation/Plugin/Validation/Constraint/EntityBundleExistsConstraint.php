<?php

declare(strict_types=1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a bundle exists on a certain content entity type.
 *
 * @Constraint(
 *   id = "EntityBundleExists",
 *   label = @Translation("Entity bundle exists", context = "Validation"),
 *   type = "entity",
 * )
 */
class EntityBundleExistsConstraint extends Constraint {

  public $message = "The '@bundle' bundle does not exist on the '@entity_type_id' entity type.";

  /**
   * Fields which must have a unique combination among all entities of a type.
   *
   * @var string[]
   */
  public string $entityTypeId;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'entityTypeId';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['entityTypeId'];
  }

}
