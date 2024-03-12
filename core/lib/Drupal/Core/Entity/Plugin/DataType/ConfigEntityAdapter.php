<?php

namespace Drupal\Core\Entity\Plugin\DataType;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataManagerInterface;

/**
 * Enhances EntityAdapter for config entities.
 */
class ConfigEntityAdapter extends EntityAdapter {

  /**
   * The wrapped entity object.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface
   */
  protected $entity;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * {@inheritdoc}
   */
  public function get($property_name) {
    if (!isset($this->entity)) {
      throw new MissingDataException("Unable to get property $property_name as no entity has been provided.");
    }
    return $this->getConfigTypedData()->get($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value, $notify = TRUE) {
    if (!isset($this->entity)) {
      throw new MissingDataException("Unable to set property $property_name as no entity has been provided.");
    }
    $this->entity->set($property_name, $value, $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties($include_computed = FALSE) {
    if (!isset($this->entity)) {
      throw new MissingDataException('Unable to get properties as no entity has been provided.');
    }
    return $this->getConfigTypedData()->getProperties($include_computed);
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name) {
    if (isset($this->entity)) {
      // Let the entity know of any changes.
      $this->getConfigTypedData()->onChange($property_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \ArrayIterator {
    if (isset($this->entity)) {
      return $this->getConfigTypedData()->getIterator();
    }
    return new \ArrayIterator([]);
  }

  /**
   * Gets the typed config manager.
   *
   * @return \Drupal\Core\Config\TypedConfigManagerInterface
   *   The typed config manager.
   */
  protected function getTypedConfigManager() {
    if (empty($this->typedConfigManager)) {
      // Use the typed data manager if it is also the typed config manager.
      // @todo Remove this in https://www.drupal.org/node/3011137.
      $typed_data_manager = $this->getTypedDataManager();
      if ($typed_data_manager instanceof TypedConfigManagerInterface) {
        $this->typedConfigManager = $typed_data_manager;
      }
      else {
        $this->typedConfigManager = \Drupal::service('config.typed');
      }
    }

    return $this->typedConfigManager;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Remove this in https://www.drupal.org/node/3011137.
   */
  public function getTypedDataManager() {
    if (empty($this->typedDataManager)) {
      $this->typedDataManager = \Drupal::service('config.typed');
    }

    return $this->typedDataManager;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Remove this in https://www.drupal.org/node/3011137.
   */
  public function setTypedDataManager(TypedDataManagerInterface $typed_data_manager) {
    $this->typedDataManager = $typed_data_manager;
    if ($typed_data_manager instanceof TypedConfigManagerInterface) {
      $this->typedConfigManager = $typed_data_manager;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // @todo Figure out what to do for this method, see
    //   https://www.drupal.org/project/drupal/issues/2945635.
    throw new \BadMethodCallException('Method not supported');
  }

  /**
   * Gets typed data for config entity.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface
   *   The typed data.
   */
  public function getConfigTypedData() {
    return $this->getTypedConfigManager()->createFromNameAndData($this->entity->getConfigDependencyName(), $this->entity->toArray());
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromEntity(EntityInterface $entity) {
    assert($entity instanceof ConfigEntityInterface);
    $instance = parent::createFromEntity($entity);

    // The ConfigEntityType definition may have validation constraints defined,
    // but so may the corresponding config schema type. Both are needed.
    $typed_config = \Drupal::service('config.typed');
    assert($typed_config instanceof TypedConfigManagerInterface);

    // The config name for a config entity always uses the config prefix. This
    // allows determining how many parts the ID consists of. Most consist
    // only one, but some consist of multiple, up to 3 in Drupal core.
    // @see \Drupal\Core\Field\FieldConfigBase::id()
    $config_name = $entity->getConfigDependencyName();
    $prefix = $entity->getEntityType()->getConfigPrefix();
    $suffix = str_replace($prefix, '', $config_name);
    // Determine the ID parts (separated by periods) in the config dependency
    // name.
    $id_part_count = substr_count($suffix, '.');
    // The config schema type is then: `<prefix>` followed by one `.*` for every
    // ID part.
    $config_schema_type = $prefix . str_repeat('.*', $id_part_count);

    $schema_defined_constraints = $typed_config->getDefinition($config_schema_type)['constraints'] ?? [];
    $definition = $instance->getDataDefinition();
    $definition->setConstraints($definition->getConstraints() + $schema_defined_constraints);

    $instance = new static($definition);
    $instance->setValue($entity);
    return $instance;
  }

}
