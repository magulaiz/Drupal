<?php

namespace Drupal\Core\Config;

use Composer\Autoload\ClassLoader;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Defines the autoloading storage.
 *
 * Allows configuration to be read that contains constants and enum from
 * extensions that are not yet installed.
 */
class AutoloadingStorage implements StorageInterface, StorageCacheInterface {
  use DependencySerializationTrait;

  /**
   * Class loader that loads code from extensions provided to the constructor.
   *
   * @var \Composer\Autoload\ClassLoader|null
   */
  protected ?ClassLoader $classloader = NULL;

  /**
   * Constructs a new AutoloadingStorage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be wrap.
   * @param string[] $newExtensions
   *   A list of new extensions in the source storage that we need to allow to
   *   autoload while reading. The values should be absolute paths to the
   *   extension directory.
   */
  public function __construct(protected StorageInterface $storage, array $newExtensions = []) {
    if (!empty($newExtensions)) {
      $this->classloader = new ClassLoader();
      foreach ($newExtensions as $extension => $dir) {
        $this->classloader->addPsr4("Drupal\\$extension\\", $dir . '/src');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return $this->storage->exists($name);
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    $this->classloader?->register(TRUE);
    $data = $this->storage->read($name);
    $this->classloader?->unregister();
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $this->classloader?->register(TRUE);
    $data = $this->storage->readMultiple($names);
    $this->classloader?->unregister();
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    return $this->storage->write($name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    return $this->storage->delete($name);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
    return $this->storage->rename($name, $new_name);
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return $this->storage->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    return $this->storage->decode($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return $this->storage->listAll($prefix);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    return $this->storage->deleteAll($prefix);
  }

  /**
   * Clears the static list cache.
   */
  public function resetListCache() {
    if ($this->storage instanceof StorageCacheInterface) {
      $this->storage->resetListCache();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    $collection = new static(
      $this->storage->createCollection($collection)
    );
    $collection->classloader = $this->classloader;
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    return $this->storage->getAllCollectionNames();
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() {
    return $this->storage->getCollectionName();
  }

}
