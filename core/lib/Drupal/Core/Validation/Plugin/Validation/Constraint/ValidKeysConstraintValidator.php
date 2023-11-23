<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

// cspell:ignore validatable

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

    $mapping = $this->context->getObject();
    assert($mapping instanceof Mapping);

    if ($constraint->allowedKeys === '<infer>') {
      $valid_keys = $mapping->getValidKeys();
      $required_keys = $mapping->getRequiredKeys();
    }
    elseif (is_array($constraint->allowedKeys)) {
      if (!empty(array_diff($constraint->allowedKeys, $mapping->getValidKeys()))) {
        throw new \InvalidArgumentException(sprintf(
          'The type \'%s\' explicitly specifies the allowed keys (%s), but they are not a subset of the statically defined mapping keys in the schema (%s).',
          $this->context->getObject()->getDataDefinition()->getDataType(),
          implode(', ', $constraint->allowedKeys),
          implode(', ', $mapping->getValidKeys())
        ));
      }
      $valid_keys = array_intersect($mapping->getValidKeys(), $constraint->allowedKeys);
      $required_keys = array_intersect($mapping->getRequiredKeys(), $constraint->allowedKeys);
    }
    else {
      throw new InvalidArgumentException("'$constraint->allowedKeys' is not a valid set of allowed keys.");
    }

    $mapping = $this->context->getObject();
    assert($mapping instanceof Mapping);
    $dynamically_valid_keys = array_merge(...array_values($mapping->getDynamicallyValidKeys()));
    $other_type_valid_keys = array_diff($dynamically_valid_keys, $valid_keys);

    // Statically valid: keys that are valid for all possible types matching the
    // type definition of this mapping.
    // For example, `block.block.*:settings` has the following statically valid
    // keys: id, label, label_display, provider, status, info, view_mode and
    // context_mapping.
    // @see \Drupal\KernelTests\Config\Schema\MappingTest::providerMappingInterpretation()
    $invalid_keys = array_diff(array_keys($value), $valid_keys, $other_type_valid_keys);
    foreach ($invalid_keys as $key) {
      $this->context->buildViolation($constraint->invalidKeyMessage)
        ->setParameter('@key', $key)
        ->atPath($key)
        ->setInvalidValue($key)
        ->addViolation();
    }
    // Dynamically valid: keys that are valid not for all possible types, but
    // for the actually resolved type definition of this mapping (in addition to
    // the statically valid keys).
    // @see \Drupal\Core\Config\Schema\Mapping::getDynamicallyValidKeys()
    // For example, `block.block.*:settings` has the following dynamically valid
    // keys when the block plugin is `system_branding_block`: use_site_logo,
    // use_site_name and use_site_slogan. But if the used block plugin is
    // `local_tasks_block`, then the dynamically valid keys are: primary,
    // secondary.
    // @see \Drupal\KernelTests\Config\Schema\MappingTest::providerMappingInterpretation()
    $dynamically_invalid_keys = array_intersect(array_keys($value), $other_type_valid_keys);
    foreach ($dynamically_invalid_keys as $key) {
      $this->context->addViolation($constraint->dynamicInvalidKeyMessage, ['@key' => $key] + self::getDynamicMessageParameters($mapping));
    }

    // All keys are optional by default (meaning they can be omitted). This is
    // unintuitive and contradicts Drupal core's documentation.
    // @see https://www.drupal.org/node/2264179
    // To gradually evolve configuration schemas in the Drupal ecosystem to be
    // validatable, this needs to be clarified in a non-disruptive way. Any
    // config schema type definition — that is, a top-level entry in a
    // *.schema.yml file — can opt into stricter behavior, whereby a key is
    // required unless it specifies `requiredKey: false`, by adding
    // `FullyValidatable` as a top-level validation constraint.
    // @see https://www.drupal.org/node/3364108
    // @see https://www.drupal.org/node/3364109
    $root_type_has_opted_in = FALSE;
    foreach ($this->context->getRoot()->getConstraints() as $c) {
      if ($c instanceof FullyValidatableConstraint) {
        $root_type_has_opted_in = TRUE;
        break;
      }
    }
    // Return early: do not generate validation errors for keys that are
    // required.
    if (!$root_type_has_opted_in) {
      return;
    }

    // Statically required: same principle as for "statically valid" above, but
    // this time restricted to the subset of statically valid keys that do not
    // have `requiredKey: false`.
    $statically_required_keys = array_diff($required_keys, $dynamically_valid_keys);
    $missing_keys = array_diff($statically_required_keys, array_keys($value));
    foreach ($missing_keys as $key) {
      $this->context->addViolation($constraint->missingRequiredKeyMessage, ['@key' => $key]);
    }
    // Dynamically required: same principle as for "dynamically valid" above,
    // but this time restricted to the subset of dynamically valid keys that do
    // not have `requiredKey: false`.
    $dynamically_required_keys = array_intersect($required_keys, $dynamically_valid_keys);
    $missing_dynamically_required_keys = array_diff($dynamically_required_keys, array_keys($value));
    foreach ($missing_dynamically_required_keys as $key) {
      $this->context->addViolation($constraint->dynamicMissingRequiredKeyMessage, ['@key' => $key] + self::getDynamicMessageParameters($mapping));
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
    assert(!in_array('%type', $instructions));

    // The %key instruction can only be used on its own. In this case, there is
    // no need to fetch a value, only the string that was used as the key is
    // responsible for determining the mapping type.
    if ($instructions === ['%key']) {
      $key = array_pop($property_path_parts);
      array_push($property_path_parts, '%key');
      $resolved_property_path = implode('.', $property_path_parts);
      return $message_parameters + [
        '@dynamic_type_property_path' => $resolved_property_path,
        '@dynamic_type_property_value' => $key,
      ];
    }

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
