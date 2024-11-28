<?php

namespace Drupal\Core\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Provides a repository for installed entity definitions.
 */
class EntityLastInstalledSchemaRepository implements EntityLastInstalledSchemaRepositoryInterface {

  /**
   * The key-value factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The loaded installed entity type definitions.
   *
   * @var array|null
   */
  protected $entityTypeDefinitions = NULL;

  /**
   * Constructs a new EntityLastInstalledSchemaRepository.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct(KeyValueFactoryInterface $key_value_factory, CacheBackendInterface $cache) {
    $this->keyValueFactory = $key_value_factory;
    $this->cacheBackend = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastInstalledDefinition($entity_type_id) {
    return $this->getLastInstalledDefinitions()[$entity_type_id] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastInstalledDefinitions() {
    if ($this->entityTypeDefinitions) {
      return $this->entityTypeDefinitions;
    }
    elseif ($cache = $this->cacheBackend->get('entity_type_definitions.installed')) {
      $this->entityTypeDefinitions = $cache->data;
      return $this->entityTypeDefinitions;
    }

    $all_definitions = $this->keyValueFactory->get('entity.definitions.installed')->getAll();

    // Filter out field storage definitions.
    $filtered_keys = array_filter(array_keys($all_definitions), function ($key) {
        return str_ends_with($key, '.entity_type');
    });
    $entity_type_definitions = array_intersect_key($all_definitions, array_flip($filtered_keys));

    // Ensure that the returned array is keyed by the entity type ID.
    $keys = array_keys($entity_type_definitions);
    $keys = array_map(function ($key) {
      $parts = explode('.', $key);
      return $parts[0];
    }, $keys);

    $this->entityTypeDefinitions = array_combine($keys, $entity_type_definitions);
    $this->cacheBackend->set('entity_type_definitions.installed', $this->entityTypeDefinitions, Cache::PERMANENT);
    return $this->entityTypeDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastInstalledDefinition(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $this->keyValueFactory->get('entity.definitions.installed')->set($entity_type_id . '.entity_type', $entity_type);
    $this->cacheBackend->delete('entity_type_definitions.installed');
    $this->entityTypeDefinitions = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLastInstalledDefinition($entity_type_id) {
    $this->keyValueFactory->get('entity.definitions.installed')->delete($entity_type_id . '.entity_type');
    // Clean up field storage definitions as well. Even if the entity type
    // isn't currently fieldable, there might be legacy definitions.
    $this->getFieldStorageDefinitionStorage($entity_type_id)->deleteAll();
    $this->cacheBackend->deleteMultiple(['entity_type_definitions.installed', $entity_type_id . '.field_storage_definitions.installed']);
    $this->entityTypeDefinitions = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastInstalledFieldStorageDefinitions($entity_type_id) {
    if ($cache = $this->cacheBackend->get($entity_type_id . '.field_storage_definitions.installed')) {
      return $cache->data;
    }
    $old_store = $this->keyValueFactory->get('entity.definitions.installed');
    if ($definitions = $old_store->get("$entity_type_id.field_storage_definitions")) {
      file_put_contents('/tmp/log', (new \Exception())->getTraceAsString(), \FILE_APPEND);
    }
    $definitions = $this->getFieldStorageDefinitionStorage($entity_type_id)->getAll();
    $this->cacheBackend->set($entity_type_id . '.field_storage_definitions.installed', $definitions, Cache::PERMANENT);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastInstalledFieldStorageDefinitions($entity_type_id, array $storage_definitions) {
    $storage = $this->getFieldStorageDefinitionStorage($entity_type_id);
    $storage->deleteAll();
    $storage->setMultiple($storage_definitions);
    $this->deleteFieldStorageDefinitionCache($entity_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function setLastInstalledFieldStorageDefinition(FieldStorageDefinitionInterface $storage_definition) {
    if ($storage_definition->getName() === 'content_translation_changed') {
      file_put_contents('/tmp/log', (new \Exception())->getTraceAsString());
    }
    $this->getFieldStorageDefinitionStorage($storage_definition->getTargetEntityTypeId())
      ->set($storage_definition->getName(), $storage_definition);
    $this->deleteFieldStorageDefinitionCache($storage_definition->getTargetEntityTypeId());
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLastInstalledFieldStorageDefinition(FieldStorageDefinitionInterface $storage_definition) {
    $this->getFieldStorageDefinitionStorage($storage_definition->getTargetEntityTypeId())
      ->delete($storage_definition->getName());
    $this->deleteFieldStorageDefinitionCache($storage_definition->getTargetEntityTypeId());
  }

  /**
   * Gets the field definition storage.
   *
   * @param string $entity_type_id
   *   The entity type id.
   *
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *   The key-value storage.
   */
  protected function getFieldStorageDefinitionStorage(string $entity_type_id): KeyValueStoreInterface {
    return $this->keyValueFactory->get("field.storage.definitions.installed.$entity_type_id");
  }

  /**
   * Deletes the cache for the field storage definition cache.
   *
   * @param string $entity_type_id
   *   The entity type id.
   *
   * @return void
   *   The void.
   */
  protected function deleteFieldStorageDefinitionCache(string $entity_type_id): void {
    $this->cacheBackend->delete($entity_type_id . '.field_storage_definitions.installed');
  }

}
