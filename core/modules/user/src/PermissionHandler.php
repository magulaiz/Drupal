<?php

namespace Drupal\user;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides the available permissions based on yml files.
 *
 * To define permissions you can use a $module.permissions.yml file. This file
 * defines machine names, human-readable names, restrict access (if required for
 * security warning), and optionally descriptions for each permission type. The
 * machine names are the canonical way to refer to permissions for access
 * checking.
 *
 * If your module needs to define dynamic permissions you can use the
 * permission_callbacks key to declare a callable that will return an array of
 * permissions, keyed by machine name. Each item in the array can contain the
 * same keys as an entry in $module.permissions.yml.
 *
 * Here is an example from the core filter module (comments have been added):
 * @code
 * # The key is the permission machine name, and is required.
 * administer filters:
 *   # (required) Human readable name of the permission used in the UI.
 *   title: 'Administer text formats and filters'
 *   # (optional) Additional description fo the permission used in the UI.
 *   description: 'Define how text is handled by combining filters into text formats.'
 *   # (optional) Boolean, when set to true a warning about site security will
 *   # be displayed on the Permissions page. Defaults to false.
 *   restrict access: false
 *
 * # An array of callables used to generate dynamic permissions.
 * permission_callbacks:
 *   # The callable should return an associative array with one or more
 *   # permissions. Each permission array can use the same keys as the example
 *   # permission defined above. Additionally, a dependencies key is supported.
 *   # For more information about permission dependencies see
 *   # PermissionHandlerInterface::getPermissions().
 *   - Drupal\filter\FilterPermissions::permissions
 * @endcode
 *
 * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
 * @see filter.permissions.yml
 * @see \Drupal\filter\FilterPermissions
 * @see user_api
 */
class PermissionHandler implements PermissionHandlerInterface {

  use StringTranslationTrait;

  private ?YamlDiscovery $yamlDiscovery = NULL;

  /**
   * Constructs a new PermissionHandler.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation.
   * @param \Drupal\Core\Utility\CallableResolver|\Drupal\Core\Controller\ControllerResolverInterface|null $controllerResolver
   *   The callable resolver.
   * @param \Drupal\user\PermissionProvidersLocator $permissionProvidersLocator
   *   Permission handler locator.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    private readonly ?ControllerResolverInterface $controllerResolver,
    private ?PermissionProvidersLocator $permissionProvidersLocator = NULL,
  ) {
    if ($controllerResolver !== NULL) {
      // use of the $controller_resolver arg is discontinued...
      @trigger_error('Calling ' . __METHOD__ . '() with the $controllerResolver argument is deprecated in drupal:10.3.0 and is removed in drupal:11.0.0. See https://www.drupal.org/node/3421573', E_USER_DEPRECATED);
    }
    if ($permissionProvidersLocator === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $permissionProvidersLocator argument is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3421573', E_USER_DEPRECATED);
      $this->permissionProvidersLocator = \Drupal::service(PermissionProvidersLocator::class);
    }
    $this->setStringTranslation($stringTranslation);
  }

  /**
   * Gets the YAML discovery.
   */
  protected function getYamlDiscovery() {
    return $this->yamlDiscovery ??= new YamlDiscovery('permissions', $this->moduleHandler->getModuleDirectories());
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissions() {
    $all_permissions = $this->buildPermissionsYaml();

    return $this->sortPermissions($all_permissions);
  }

  /**
   * {@inheritdoc}
   */
  public function moduleProvidesPermissions($module_name) {
    // @TODO Static cache this information, see
    // https://www.drupal.org/node/2339487
    $permissions = $this->getPermissions();

    foreach ($permissions as $permission) {
      if ($permission['provider'] == $module_name) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Builds all permissions provided by .permissions.yml files.
   *
   * @return array<string, array{title: \Drupal\Core\StringTranslation\TranslatableMarkup, provider: string, description: \Drupal\Core\StringTranslation\TranslatableMarkup|null}>
   *   An array with the same structure as
   *   PermissionHandlerInterface::getPermissions().
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  protected function buildPermissionsYaml() {
    $allPermissions = [];

    foreach ($this->permissionProvidersLocator->getPermissionProviders() as [$provider, $permissionProvider]) {
      foreach ($permissionProvider() as $permissionName => $permission) {
        if (is_string($permission)) {
          $permission = ['title' => $permission];
        }

        $allPermissions[$permissionName] = $permission + [
          'description' => NULL,
          'provider' => $provider,
        ];
      }
    }

    foreach ($this->getYamlDiscovery()->findAll() as $provider => $permissions) {
      foreach ($permissions as $permissionName => $permission) {
        if ($permissionName === 'permission_callbacks') {
          continue;
        }

        if (is_string($permission)) {
          $permission = ['title' => $permission];
        }

        $permission['title'] = $this->t($permission['title']);
        $permission['description'] = isset($permission['description']) ? $this->t($permission['description']) : NULL;
        $permission['provider'] = !empty($permission['provider']) ? $permission['provider'] : $provider;

        $allPermissions[$permissionName] = $permission;
      }
    }

    return $allPermissions;
  }

  /**
   * Sorts the given permissions by provider name and title.
   *
   * @param array $all_permissions
   *   The permissions to be sorted.
   *
   * @return array[]
   *   An array with the same structure as
   *   PermissionHandlerInterface::getPermissions().
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  protected function sortPermissions(array $all_permissions = []) {
    // Get a list of all the modules providing permissions and sort by
    // display name.
    $modules = $this->getModuleNames();

    uasort($all_permissions, function (array $permission_a, array $permission_b) use ($modules) {
      if ($modules[$permission_a['provider']] == $modules[$permission_b['provider']]) {
        return $permission_a['title'] <=> $permission_b['title'];
      }
      else {
        return $modules[$permission_a['provider']] <=> $modules[$permission_b['provider']];
      }
    });
    return $all_permissions;
  }

  /**
   * Returns all module names.
   *
   * @return string[]
   *   Returns the human readable names of all modules keyed by machine name.
   */
  protected function getModuleNames() {
    $modules = [];
    foreach (array_keys($this->moduleHandler->getModuleList()) as $module) {
      $modules[$module] = $this->moduleHandler->getName($module);
    }
    asort($modules);
    return $modules;
  }

}
