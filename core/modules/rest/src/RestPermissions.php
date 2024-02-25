<?php

namespace Drupal\rest;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\Type\ResourcePluginManager;

/**
 * Provides rest module permissions.
 */
class RestPermissions {

  /**
   * The REST resource config storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $resourceConfigStorage;

  /**
   * Constructs a new RestPermissions instance.
   */
  public function __construct(
    private readonly ResourcePluginManager $restPluginManager,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->resourceConfigStorage = $entityTypeManager->getStorage('rest_resource_config');
  }

  /**
   * Returns an array of REST permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];
    /** @var \Drupal\rest\RestResourceConfigInterface[] $resource_configs */
    $resource_configs = $this->resourceConfigStorage->loadMultiple();
    foreach ($resource_configs as $resource_config) {
      $plugin = $resource_config->getResourcePlugin();

      // Add the rest resource configuration entity as a dependency to the
      // permissions.
      $permissions += array_map(function (array $permission_info) use ($resource_config) {
        $merge_info['dependencies'][$resource_config->getConfigDependencyKey()] = [
          $resource_config->getConfigDependencyName(),
        ];
        return NestedArray::mergeDeep($permission_info, $merge_info);
      }, $plugin->permissions());
    }
    return $permissions;
  }

}
