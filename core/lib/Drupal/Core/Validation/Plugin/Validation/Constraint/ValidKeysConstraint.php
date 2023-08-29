<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Checks that all the keys of a mapping are known.
 *
 * @Constraint(
 *   id = "ValidKeys",
 *   label = @Translation("Valid mapping keys", context = "Validation"),
 * )
 */
class ValidKeysConstraint extends Constraint {

  /**
   * The error message if an invalid key appears.
   *
   * @var string
   */
  public string $invalidKeyMessage = "'@key' is not a supported key.";

  /**
   * The error message if the array being validated is a list.
   *
   * @var string
   */
  public string $indexedArrayMessage = 'Numerically indexed arrays are not allowed.';

  /**
   * Keys which are allowed in the validated array, or `<infer>` to auto-detect.
   *
   * @var array|string
   */
  public array|string $allowedKeys;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'allowedKeys';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['allowedKeys'];
  }

  /**
   * Returns the list of valid keys.
   *
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   *   The current execution context.
   *
   * @return string[]
   *   The keys that will be considered valid.
   */
  public function getAllowedKeys(ExecutionContextInterface $context): array {
    // If we were given an explicit array of allowed keys, return that.
    if (is_array($this->allowedKeys)) {
      return $this->allowedKeys;
    }
    // The only other value we'll accept is the string `<infer>`.
    elseif ($this->allowedKeys === '<infer>') {
      $mapping = $context->getObject();
      assert($mapping instanceof Mapping);
      return $mapping->getValidKeys();
    }
    throw new InvalidArgumentException("'$this->allowedKeys' is not a valid set of allowed keys.");
  }

}
