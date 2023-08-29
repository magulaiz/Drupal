<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\TypedData\MapDataDefinition;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the RequiredKeys constraint.
 */
class RequiredKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof RequiredKeysConstraint);

    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    // The only value currently supported is the string `<infer>`.
    if ($constraint->requiredKeys !== '<infer>') {
      throw new \DomainException("Only '<infer>' is allowed.");
    }

    $mapping = $this->context->getObject();
    assert($mapping instanceof Mapping);
    $required_keys = $mapping->getRequiredKeys();
    $conditionally_optional_keys = $mapping->getConditionallyOptionalKeys();

    // Unconditionally required: required here and not optional anywhere.
    $unconditional = array_diff($required_keys, $conditionally_optional_keys);
    $missing_keys = array_diff($unconditional, array_keys($value));
    foreach ($missing_keys as $key) {
      $this->context->addViolation($constraint->message, ['@key' => $key]);
    }

    // Conditionally required: required here and optional somewhere (but not
    // everywhere).
    $conditional = array_intersect($required_keys, $conditionally_optional_keys);
    $missing_conditional_keys = array_diff($conditional, array_keys($value));
    foreach ($missing_conditional_keys as $key) {
      $this->context->addViolation($constraint->conditionalMessage, ['@key' => $key] + self::getConditionalMessageParameters($mapping, $key));
    }

    // Extraneous: optional here and required somewhere (but not everywhere).
    $extraneous = array_diff($conditionally_optional_keys, $required_keys);
    $extraneous_keys = array_intersect($extraneous, array_keys($value));
    foreach ($extraneous_keys as $key) {
      $this->context->addViolation($constraint->extraneousMessage, ['@key' => $key] + self::getConditionalMessageParameters($mapping, $key));
    }
  }

  /**
   * Computes message parameters for $conditionalMessage and $extraneousMessage.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $mapping
   *   A `type: mapping` instance, with values.
   * @param string $key
   *   The key whose conditional message parameters to compute.
   *
   * @return array
   *   An array containing the following message parameters:
   *   - '@original_dynamic_type': original dynamic type
   *   - '@resolved_dynamic_type': resolved dynamic type
   *   - '@condition_property_path': (relative) property path of the condition
   *   - '@condition_property_value': value of the condition
   */
  protected static function getConditionalMessageParameters(Mapping $mapping, string $key): array {
    $definition = $mapping->getDataDefinition();
    assert($definition instanceof MapDataDefinition);
    $definition = $definition->toArray();
    assert(array_key_exists('mapping', $definition));

    // The original (dynamic) type (defined on a key in this mapping).
    $original_type = $definition['mapping'][$key]['type'];

    // The resolved type.
    $resolved_type = $mapping->getResolvedDataDefinitions()[$key]->getDataType();

    // $original_type must be a dynamic type and the resolved type must be
    // different and not be dynamic.
    assert(strpos($original_type, ']'));
    assert($original_type !== $resolved_type);
    assert(!strpos($resolved_type, ']'));

    $message_parameters = [
      '@original_dynamic_type' => $original_type,
      '@resolved_dynamic_type' => $resolved_type,
    ];

    $config = $mapping->getRoot();
    // Determine the absolute property path to the given key.
    $absolute_property_path_key = sprintf("%s.%s", $mapping->getPropertyPath(), $key);
    // Strip the prefix for the containing config.
    $property_path_start = substr($absolute_property_path_key, strlen($config->getName()) + 1);

    // Extract the instructions stored in the dynamic type.
    $matches = [];
    // @see \Drupal\Core\Config\TypedConfigManager::replaceName()
    assert(preg_match("/\[(.*)\]/U", $original_type, $matches) === 1);
    // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
    $instructions = explode('.', $matches[1]);

    // Start from the relative path for the key and follow the instructions.
    $property_path_parts = explode('.', $property_path_start);
    while ($instructions) {
      $instruction = array_shift($instructions);
      switch ($instruction) {
        case '%parent';
          array_pop($property_path_parts);
          break;

        default:
          array_push($property_path_parts, $instruction);
          break;
      }
    }
    $resolved_property_path = implode('.', $property_path_parts);
    $message_parameters += [
      '@condition_property_path' => $resolved_property_path,
    ];
    try {
      $val = $config->get($resolved_property_path)->getValue();
      // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
      $val = is_bool($val) ? (int) $val : $val;
      return $message_parameters + [
        '@condition_property_value' => $val,
      ];
    }
    catch (\InvalidArgumentException) {
      return $message_parameters + [
        '@condition_property_value' => '<absent>',
      ];
    }
  }

}
