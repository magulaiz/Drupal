<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Defines an abstract test base for entity kernel tests.
 */
abstract class EntityKernelTestBase extends KernelTestBase {
  use UserCreationTrait {
    checkPermissions as drupalCheckPermissions;
    createAdminRole as drupalCreateAdminRole;
    createRole as drupalCreateRole;
    createUser as drupalCreateUser;
    grantPermissions as drupalGrantPermissions;
    setCurrentUser as drupalSetCurrentUser;
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'user',
    'system',
    'field',
    'text',
    'filter',
    'entity_test',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A list of generated identifiers.
   *
   * @var array
   */
  protected $generatedIds = [];

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->state = $this->container->get('state');

    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');

    // If the concrete test sub-class installs the Node or Comment modules,
    // ensure that the node and comment entity schema are created before the
    // field configurations are installed. This is because the entity tables
    // need to be created before the body field storage tables. This prevents
    // trying to create the body field tables twice.
    $class = static::class;
    while ($class) {
      if (property_exists($class, 'modules')) {
        // Only check the modules, if the $modules property was not inherited.
        $rp = new \ReflectionProperty($class, 'modules');
        if ($rp->class == $class) {
          foreach (array_intersect(['node', 'comment'], $class::$modules) as $module) {
            $this->installEntitySchema($module);
          }
        }
      }
      $class = get_parent_class($class);
    }

    $this->installConfig(['field']);
  }

  /**
   * Creates a user.
   *
   * @param array $permissions
   *   Array of permission names to assign to user. Note that the user always
   *   has the default permissions derived from the "authenticated users" role.
   * @param string $name
   *   The user name.
   * @param bool $admin
   *   (optional) Whether the user should be an administrator
   *   with all the available permissions.
   * @param array $values
   *   (optional) An array of initial user field values.
   *
   * @return \Drupal\user\Entity\User
   *   The created user entity.
   */
  protected function createUser(array $permissions = [], $name = NULL, bool $admin = FALSE, array $values = []) {
    return $this->drupalCreateUser($permissions, $name, $admin, $values);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpCurrentUser(array $values = [], array $permissions = [], $admin = FALSE) {
    $values += [
      'name' => $this->randomMachineName(),
    ];

    // In many cases the anonymous user account is fine for testing purposes,
    // however, if we need to create a user with a non-empty ID, we need also
    // the "sequences" table.
    if (!\Drupal::moduleHandler()->moduleExists('system')) {
      $values['uid'] = 0;
    }

    // Creating an administrator or assigning custom permissions would result in
    // creating and assigning a new role to the user. This is not possible with
    // the anonymous user account.
    if (($admin || $permissions) && isset($values['uid']) && is_numeric($values['uid']) && $values['uid'] == 0) {
      throw new \LogicException('The anonymous user account cannot have additional roles.');
    }

    $original_permissions = $permissions;
    $original_values = $values;
    $autocreate_user_1 = !isset($values['uid']) || $values['uid'] > 1;

    // No need to create user account 1 if it already exists.
    try {
      $autocreate_user_1 = $autocreate_user_1 && !User::load(1);
    }
    catch (DatabaseExceptionWrapper $e) {
      // Missing schema, it will be created later on.
    }

    // Save the user entity object and created its schema if needed.
    try {
      if ($autocreate_user_1) {
        $permissions = [];
        $values = [];
      }
      $user = $this->createUser($permissions, NULL, FALSE, $values);
    }
    catch (EntityStorageException $e) {
      if ($this instanceof KernelTestBase) {
        $this->installEntitySchema('user');
        $user = $this->createUser($permissions, NULL, FALSE, $values);
      }
      else {
        throw $e;
      }
    }

    // Ensure the anonymous user account exists.
    if (!User::load(0)) {
      $values = [
        'uid' => 0,
        'status' => 0,
        'name' => '',
      ];
      User::create($values)->save();
    }

    // If we automatically created user account 1, we need to create a regular
    // user account before setting up the current user service to avoid
    // potential false positives caused by access control bypass.
    if ($autocreate_user_1) {
      $user = $this->createUser($original_permissions, NULL, TRUE, $original_values);
    }

    $this->setCurrentUser($user);

    return $user;
  }

  /**
   * Reloads the given entity from the storage and returns it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be reloaded.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The reloaded entity.
   */
  protected function reloadEntity(EntityInterface $entity) {
    $controller = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
    $controller->resetCache([$entity->id()]);
    return $controller->load($entity->id());
  }

  /**
   * Returns the entity_test hook invocation info.
   *
   * @return array
   *   An associative array of arbitrary hook data keyed by hook name.
   */
  protected function getHooksInfo() {
    $key = 'entity_test.hooks';
    $hooks = $this->state->get($key);
    $this->state->set($key, []);
    return $hooks;
  }

  /**
   * Installs a module and refreshes services.
   *
   * @param string $module
   *   The module to install.
   */
  protected function installModule($module) {
    $this->enableModules([$module]);
    $this->refreshServices();
  }

  /**
   * Uninstalls a module and refreshes services.
   *
   * @param string $module
   *   The module to uninstall.
   */
  protected function uninstallModule($module) {
    $this->disableModules([$module]);
    $this->refreshServices();
  }

  /**
   * Refresh services.
   */
  protected function refreshServices() {
    $this->container = \Drupal::getContainer();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->state = $this->container->get('state');
  }

  /**
   * Generates a random ID avoiding collisions.
   *
   * @param bool $string
   *   (optional) Whether the id should have string type. Defaults to FALSE.
   *
   * @return int|string
   *   The entity identifier.
   */
  protected function generateRandomEntityId($string = FALSE) {
    srand(time());
    do {
      // 0x7FFFFFFF is the maximum allowed value for integers that works for all
      // Drupal supported databases and is known to work for other databases
      // like SQL Server 2014 and Oracle 10 too.
      $id = $string ? $this->randomMachineName() : mt_rand(1, 0x7FFFFFFF);
    } while (isset($this->generatedIds[$id]));
    $this->generatedIds[$id] = $id;
    return $id;
  }

}
