<?php

namespace Drupal\Core\Field\TypedData;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Implements handling of options provider for field definitions.
 *
 * The trait expects the object to implement
 * \Drupal\Core\Field\FieldDefinitionInterface or
 * \Drupal\Core\Field\FieldStorageDefinitionInterface.
 */
trait FieldDefinitionOptionsProviderTrait {

  /**
   * Checks options providers can be defined for the given property.
   *
   * @param string $property_name
   *   The property for which to specify the options provider.
   * @param string|null $provider_definition
   *   The options provider definition; e.g. the class name. See
   *   \Drupal\Core\TypedData\TypedDataManager::getOptionsProvider() for
   *   supported notations.
   *
   * @throws \InvalidArgumentException
   *   Thrown if options providers cannot be defined for the given property.
   */
  protected function checkOptionsProviderDefinition($property_name, $provider_definition) {
    // Make sure the specified property supports defining option providers:
    $property_definition = $this
      ->getRelatedFieldStorageDefinition()
      ->getPropertyDefinition($property_name);

    if (!$property_definition) {
      throw new \InvalidArgumentException(sprintf(
        'Options provider for the unknown property %s of field %s given.',
        $property_name,
        $this->getRelatedFieldStorageDefinition()->getName()
      ));
    }
    if (!method_exists($property_definition, 'setOptionsProviderDefinition')) {
      throw new \InvalidArgumentException(sprintf('Unable to set an options provider for the property %s of field %s as the property definition class does not support it.',
        $property_name,
        $this->getRelatedFieldStorageDefinition()->getName()
      ));
    }
  }

  /**
   * Provides the options provider for the field.
   *
   * Implements \Drupal\Core\Field\FieldDefinitionInterface::getOptionsProvider().
   * Implements \Drupal\Core\Field\FieldStorageDefinitionInterface::getOptionsProvider().
   *
   * @param string|null $property_name
   *   The name of the property, or NULL to use the main property.
   * @param \Drupal\Core\Entity\FieldableEntityInterface|null $entity
   *   (optional) The entity for which to get the options, or NULL.
   * @param int $delta
   *   (optional) The delta of the field item. Defaults to 0.
   *
   * @return \Drupal\Core\TypedData\OptionsProviderInterface|null
   *   The options provider for the property, or NULL if not available.
   *
   * @throws \InvalidArgumentException
   *   Thrown when an invalid property name is given.
   */
  public function getOptionsProvider($property_name = NULL, ?FieldableEntityInterface $entity = NULL, $delta = 0) {
    // In order to be compatible with
    // \Drupal\Core\TypedData\DataDefinitionInterface::getOptionsProvider() we
    // must support an optional $data argument instead of the $property_name.
    // @todo Fix by resolving https://www.drupal.org/node/2268049.
    if (isset($property_name) && $property_name instanceof TypedDataInterface) {
      $entity = $property_name->getRoot();
      $property_name = NULL;
    }
    $field_storage_definition = $this->getRelatedFieldStorageDefinition();
    $property_name = $property_name ?: $field_storage_definition->getMainPropertyName();
    $property_definition = $field_storage_definition->getPropertyDefinition($property_name);

    if (!isset($property_definition)) {
      throw new \InvalidArgumentException(sprintf('Invalid property name %s given.', $property_name));
    }

    if ($entity) {
      $field_item_list = $entity->get($this->getRelatedFieldStorageDefinition()->getName());
      // Pass on an empty property object even if no data is set at the given
      // delta as this is required by the legacy API support.
      // @see \Drupal\Core\Field\TypedData\LegacyOptionsProvider
      $item = $field_item_list[$delta] ??
        \Drupal::service('plugin.manager.field.field_type')->createFieldItem($field_item_list, 0);
      $property = $item->get($property_name);
    }
    else {
      $property = NULL;
    }
    return $property_definition->getOptionsProvider($property);
  }

  /**
   * Provides the options provider definition for the field.
   *
   * Implements \Drupal\Core\Field\FieldDefinitionInterface::getOptionsProviderDefinition().
   * Implements \Drupal\Core\Field\FieldStorageDefinitionInterface::getOptionsProviderDefinition().
   *
   * @param string|null $property_name
   *   (optional) The name of the property for which to get the options provider.
   *   Defaults to NULL.
   *
   * @return array|null
   *   The options provider definition or NULL if no provider is defined.
   */
  public function getOptionsProviderDefinition($property_name = NULL) {
    $field_storage_definition = $this->getRelatedFieldStorageDefinition();
    if ($property_name == NULL && $this instanceof ListDataDefinitionInterface) {
      return $field_storage_definition->get('options_provider');
    }
    $property_name = $property_name ?: $this->getRelatedFieldStorageDefinition()->getMainPropertyName();
    return $this
      ->getRelatedFieldStorageDefinition()
      ->getPropertyDefinition($property_name)
      ->getOptionsProviderDefinition();
  }

  /**
   * Gets the field storage definition related to the object.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface
   *   The field storage definition.
   */
  private function getRelatedFieldStorageDefinition() {
    if ($this instanceof FieldStorageDefinitionInterface) {
      return $this;
    }
    elseif ($this instanceof FieldDefinitionInterface) {
      // On field definition interface, there is already a getter we can re-use.
      return $this->getFieldStorageDefinition();
    }
    throw new \LogicException(sprintf('The "%s" object (class %s) must either implement FieldDefinitionInterface or FieldStorageDefinitionInterface.',
      (string) $this->getLabel(), $this->getClass()));
  }

  /**
   * Returns the field type plugin manager.
   *
   * @return \Drupal\Core\Field\FieldTypePluginManagerInterface
   *   The field type plugin manager.
   */
  protected function getFieldTypePluginManager() {
    return \Drupal::service('plugin.manager.field.field_type');
  }

  /**
   * Adds a legacy options provider definition if necessary.
   *
   * For BC we support field item classes implementing the options provider
   * interface.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $property_definitions
   *   The field item property definitions.
   */
  protected function addLegacyOptionsProvider(array $property_definitions) {
    $main_property = $this->getRelatedFieldStorageDefinition()->getMainPropertyName();
    if (!isset($property_definitions[$main_property])) {
      return;
    }
    $property = $property_definitions[$main_property];
    $type_definition = $this->getFieldTypePluginManager()
      ->getDefinition($property->getType());

    // For BC, if the field item class implements the options provider
    // interface, specify a class that proxies it through.
    if (is_subclass_of($type_definition['class'], OptionsProviderInterface::class) && !$property->getOptionsProviderDefinition()) {
      $property->setOptionsProviderDefinition(LegacyOptionsProvider::class);
    }
  }

  /**
   * Adds the field storage definition as available options provider context.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $property_definitions
   *   The properties to which to add the context.
   */
  protected function addFieldStorageDefinitionContext(array $property_definitions) {
    $field_storage_definition = $this->getRelatedFieldStorageDefinition();
    foreach ($property_definitions as $property_definition) {
      $property_definition->setOptionsProviderContext(
        FieldStorageDefinitionAwareOptionsProviderInterface::class,
        'setFieldStorageDefinition',
        [$field_storage_definition]
      );
    }
  }

}
