<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Component\Plugin\Attribute\AttributeBase;
use Drupal\Component\Plugin\Attribute\PluginPropertyInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add form handler properties to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class EntityTypeProperty extends AttributeBase implements PluginPropertyInterface {

  /**
   * Constructs a EntityTypeProperty attribute.
   *
   * @param int|string|array $key
   *   The key within the plugin definition of the property to set. If the key
   *   is an array, it will be treated as a nested key, with the outermost keys
   *   coming first.
   * @param mixed $value
   *   The value to be set for the plugin definition property.
   */
  public function __construct(
    public readonly int|string|array $key,
    public readonly mixed $value,
  ) {
    if ($key === 'id' || $key === ['id']) {
      throw new \InvalidArgumentException('Can not use EntityTypeProperty attribute to set entity type ID.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getKey(): int|string|array {
    return $this->key;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(): mixed {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidPluginClass(string $pluginClass): bool {
    return is_a($pluginClass, EntityType::class, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(array|object $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }

    $value = $this->getValue();
    $key = $this->getKey();
    $key = is_array($key) ? $key : [$key];
    $outerKey = reset($key);
    $property = $definition->get($outerKey);

    // Key is nested if it is an array and has more than one item.
    $nested = is_array($key) && ((count($key) > 1));
    if (!$nested) {
      if (!is_null($property) && !is_array($property) && is_array($value)) {
        // Can not set an array value for a non-array property.
        throw new InvalidPluginDefinitionException($definition->id(), sprintf('Invalid property key %s specified for %s entity type definition in %s.', implode(', ', $key), $definition->id(), $this->getClass()));
      }
      return $definition->set($outerKey, $value);
    }

    if (!is_null($property) && !is_array($property)) {
      // Nested key is invalid if property exists and is not an array.
      throw new InvalidPluginDefinitionException($definition->id(), sprintf('Invalid property key %s specified for %s entity type definition in %s.', implode(', ', $key), $definition->id(), $this->getClass()));
    }
    $property = $property ?? [];
    $subKey = array_slice($key, 1);
    NestedArray::setValue($property, $subKey, $value);
    return $definition->set($outerKey, $property);
  }

}
