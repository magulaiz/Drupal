<?php

namespace Drupal\Core\TypedData\Validation;

// cspell:ignore notblank

use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Validation\Plugin\Validation\Constraint\NotNullConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\CascadingStrategy;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Mapping\TraversalStrategy;

/**
 * Validator metadata for typed data objects.
 *
 * @see \Drupal\Core\TypedData\Validation\RecursiveValidator::getMetadataFor()
 */
class TypedDataMetadata implements MetadataInterface {

  /**
   * The typed data object the metadata is about.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $typedData;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $typed_data
   *   The typed data object the metadata is about.
   */
  public function __construct(TypedDataInterface $typed_data) {
    $this->typedData = $typed_data;
  }

  /**
   * {@inheritdoc}
   */
  public function findConstraints($group): array {
    return $this->getConstraints();
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints(): array {
    $constraints = $this->typedData->getConstraints();

    // Prevent a validation error from NotBlank when NotNull already generates
    // one: when both NotBlank and NotNull are present, NotBlank should allow
    // a NULL value, otherwise there will be two validation errors with distinct
    // messages for the exact same problem. Automatically configuring NotBlank's
    // `allowNull: true` option mitigates that.
    $notnull_index = $notblank_index = NULL;
    foreach ($constraints as $index => $constraint) {
      if ($constraint instanceof NotNullConstraint) {
        $notnull_index = $index;
      }
      if ($constraint instanceof NotBlank) {
        $notblank_index = $index;
      }
      if (isset($notnull_index) && isset($notblank_index)) {
        break;
      }
    }
    if (isset($notnull_index) && isset($notblank_index)) {
      assert($constraints[$notblank_index] instanceof NotBlank);
      $constraints[$notblank_index]->allowNull = TRUE;
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getTraversalStrategy(): int {
    return TraversalStrategy::NONE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCascadingStrategy(): int {
    // By default, never cascade into validating referenced data structures.
    return CascadingStrategy::NONE;
  }

}
