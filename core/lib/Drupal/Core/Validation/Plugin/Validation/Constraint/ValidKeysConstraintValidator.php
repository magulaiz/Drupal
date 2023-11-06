<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\Schema\SequenceDataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ValidKeys constraint.
 */
class ValidKeysConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    assert($constraint instanceof ValidKeysConstraint);

    if (!is_array($value)) {
      throw new UnexpectedTypeException($value, 'array');
    }

    // Indexed arrays are invalid by definition. array_is_list() returns TRUE
    // for empty arrays, so only do this check if $value is not empty.
    if ($value && array_is_list($value)) {
      $this->context->addViolation($constraint->indexedArrayMessage);
      return;
    }

    if ($constraint->allowedKeys === '<infer>') {
      $mapping = $this->context->getObject();
      assert($mapping instanceof Mapping);

      // First: valid keys.
      $valid_keys = $mapping->getValidKeys();
      $dynamically_valid_keys = array_merge(...array_values($mapping->getDynamicallyValidKeys()));
      $other_type_valid_keys = array_diff($dynamically_valid_keys, $valid_keys);

      // Statically valid: valid here and not dynamically valid.
      $invalid_keys = array_diff(array_keys($value), $valid_keys, $other_type_valid_keys);
      foreach ($invalid_keys as $key) {
        $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
      }

      // Dynamically valid: not valid here but valid for some resolved types.
      $dynamic_invalid_keys = array_intersect(array_keys($value), $other_type_valid_keys);
      foreach ($dynamic_invalid_keys as $key) {
        $this->context->addViolation($constraint->dynamicInvalidKeyMessage, ['@key' => $key] + self::getDynamicMessageParameters($mapping));
      }

      // Second: required keys.
      $required_keys = $mapping->getRequiredKeys();
      $dynamically_valid_keys = array_merge(...array_values($mapping->getDynamicallyValidKeys()));

      // Statically required: required here and not dynamically valid.
      $statically_required_keys = array_diff($required_keys, $dynamically_valid_keys);
      $missing_keys = array_diff($statically_required_keys, array_keys($value));
      foreach ($missing_keys as $key) {
        $this->context->addViolation($constraint->requiredKeyMessage, ['@key' => $key]);
      }

      // Dynamically required: required here but not for all resolved types.
      $conditional = array_intersect($required_keys, $dynamically_valid_keys);
      $missing_conditional_keys = array_diff($conditional, array_keys($value));
      foreach ($missing_conditional_keys as $key) {
        $this->context->addViolation($constraint->dynamicRequiredKeyMessage, ['@key' => $key] + self::getDynamicMessageParameters($mapping));
      }
    }
    elseif (is_array($constraint->allowedKeys)) {
      $invalid_keys = array_diff(array_keys($value), $constraint->allowedKeys);
      foreach ($invalid_keys as $key) {
        $this->context->addViolation($constraint->invalidKeyMessage, ['@key' => $key]);
      }
    }
    else {
      throw new InvalidArgumentException("'$constraint->allowedKeys' is not a valid set of allowed keys.");
    }
  }

  /**
   * Computes message parameters for $conditionalMessage.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $mapping
   *   A `type: mapping` instance, with values.
   *
   * @return array
   *   An array containing the following message parameters:
   *   - '@original_dynamic_type': original dynamic type
   *   - '@resolved_dynamic_type': resolved dynamic type
   *   - '@dynamic_type_property_path': (relative) property path of the condition
   *   - '@dynamic_type_property_value': value of the condition
   */
  protected static function getDynamicMessageParameters(Mapping $mapping): array {
    $definition = $mapping->getDataDefinition();
    assert($definition instanceof MapDataDefinition);
    $definition = $definition->toArray();
    assert(array_key_exists('mapping', $definition));

    // The original mapping definition is used to determine the original type.
    // f.e.:
    // 1. `type: editor.settings.[%parent.editor]`
    // 2. `type: editor.image_upload_settings.[status]`.
    $parent_data_def = $mapping->getParent()->getDataDefinition();
    $original_type = match (TRUE) {
      $parent_data_def instanceof MapDataDefinition => $parent_data_def->toArray()['mapping'][$mapping->getName()]['type'],
      $parent_data_def instanceof SequenceDataDefinition => $parent_data_def->toArray()['sequence']['type'],
      default => throw new \LogicException('Invalid config schema detected.'),
    };
    $resolved_type = $definition['type'];

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
    // Find the relative property path where this mapping starts.
    $property_path_mapping = substr($mapping->getPropertyPath(), strlen($config->getName()) + 1);

    // Extract the variable values stored in the dynamic type.
    $matches = [];
    // @see \Drupal\Core\Config\TypedConfigManager::replaceName()
    assert(preg_match("/\[(.*)\]/U", $original_type, $matches) === 1);
    // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
    $variable_value = $matches[1];
    // From the variable value, extract the instructions for where to retrieve a
    // value.
    $instructions = explode('.', $variable_value);

    // Determine the property path to the configuration key that has determined
    // this type.
    // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
    $property_path_parts = explode('.', $property_path_mapping);
    // @see \Drupal\Core\Config\Schema\Mapping::getDynamicallyValidKeys()
    assert(!in_array(['%key', '%type'], $instructions));
    // Do not replace variables, do not traverse the tree of data, but instead
    // resolve the property path that contains the value causing this particular
    // type to be selected.
    while ($instructions) {
      $instruction = array_shift($instructions);
      // Go up one level: remove the last part of the property path.
      if ($instruction === '%parent') {
        array_pop($property_path_parts);
      }
      // Go down one level: append the given key.
      else {
        array_push($property_path_parts, $instruction);
      }
    }
    $resolved_property_path = implode('.', $property_path_parts);
    $message_parameters += [
      '@dynamic_type_property_path' => $resolved_property_path,
    ];

    // Determine the corresponding value for that property path.
    $val = $config->get($resolved_property_path)->getValue();
    // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
    $val = is_bool($val) ? (int) $val : $val;
    return $message_parameters + [
        '@dynamic_type_property_value' => $val,
      ];
  }

}
