<?php

namespace Drupal\Core\Config;

/**
 * Utility trait to copy configuration from one storage to another.
 */
trait StorageCopyTrait {

  /**
   * Copy the configuration from one storage to another and remove stale items.
   *
   * This method empties target storage and copies all collections from source.
   * Configuration is only copied and not imported, should not be used
   * with the active storage as the target.
   *
   * @param \Drupal\Core\Config\StorageInterface $source
   *   The configuration storage to copy from.
   * @param \Drupal\Core\Config\StorageInterface $target
   *   The configuration storage to copy to.
   */
  protected static function replaceStorageContents(StorageInterface $source, StorageInterface &$target) {

    // Copy all the configuration from all the collections.
    foreach (array_merge([StorageInterface::DEFAULT_COLLECTION], $source->getAllCollectionNames()) as $collection) {
      $source_collection = $source->createCollection($collection);
      $target_collection = $target->createCollection($collection);
      $sourceAll = $source_collection->listAll();
      foreach ($sourceAll as $name) {
        $data = $source_collection->read($name);
        $currentContents = $target_collection->read($name);
        if ($currentContents == $data) {
          continue;
        }
        if ($data !== FALSE) {
          $target_collection->write($name, $data);
        }
        else {
          \Drupal::logger('config')->notice('Missing required data for configuration: %config', [
            '%config' => $name,
          ]);
        }
      }
      $source_collection_values = array_flip($sourceAll);
      foreach ($target_collection->listAll() as $name) {
        if (!isset($source_collection_values[$name])) {
          $target_collection->delete($name);
        }
      }
    }

    // Make sure that the target is set to the same collection as the source.
    $target = $target->createCollection($source->getCollectionName());
  }

}
