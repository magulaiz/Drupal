<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Entity\Plugin\DataType\ConfigEntityAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the StringParts constraint.
 */
class StringPartsConstraintValidator extends ConstraintValidator {

  use TreeAwareConstraintTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    if (!is_string($value)) {
      throw new UnexpectedTypeException($value, 'string');
    }

    // Find the parent mapping.
    $mapping = $this->getParentProperty();
    // If it's not a Mapping nor a Config Entity, it's a logical error in the
    // config schema, not the concrete config.
    if (!$mapping instanceof Mapping && !$mapping instanceof ConfigEntityAdapter) {
      throw new \LogicException('This constraint can only be set on `type: mapping`.');
    }

    // Verify the required parts are present; if not, that's a logical error in
    // the config schema, not in concrete config.
    $properties = $mapping->getProperties();
    $missing_properties = array_diff($constraint->parts, array_keys($properties));
    if (!empty($missing_properties)) {
      throw new \LogicException(sprintf('This validation constraint is configured to inspect the properties %s, but some do not exist: %s.',
        implode(', ', $constraint->parts),
        implode(', ', $missing_properties)
      ));
    }

    // Retrieve the parts of the expected string.
    $expected_string_parts = [];
    foreach ($constraint->parts as $part) {
      $part_value = $properties[$part]->getValue();
      if (!is_string($part_value)) {
        throw new \LogicException(sprintf('The "%s" property does not contain a string, but a %s: "%s".', $part, gettype($part_value), (string) $part_value));
      }
      $expected_string_parts[] = $part_value;
    }
    $expected_string = implode($constraint->separator, $expected_string_parts);

    if ($expected_string !== $value) {
      $expected_format = implode(
        $constraint->separator,
        array_map(function (string $v) {
          return sprintf('<%s>', $v);
        }, $constraint->parts)
      );
      $this->context->addViolation($constraint->message, [
        '@value' => $value,
        '@expected_string' => $expected_string,
        '@expected_format' => $expected_format,
      ]);
    }
  }

}
