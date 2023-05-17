<?php

namespace Drupal\KernelTests\Core\Update;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Deprecation tests cases for the update.inc functions.
 *
 * @package Drupal\KernelTests\Core\Update
 *
 * @group legacy
 */
class UpdateLegacyTest extends KernelTestBase {

  use UserCreationTrait;

  const UPDATE_N = 10099;

  protected static $modules = ['system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();

    // Include the legacy update.inc file.
    include_once $this->root . '/core/includes/update.inc';
  }

  /**
   * Tests update_check_incompatibility() function.
   */
  public function testUpdateCheckIncompatibility() {
    $this->expectDeprecation('_update_fix_missing_schema() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\DeprecatedUpdate::fixMissingSchema() instead. See https://www.drupal.org/node/3013060');
    _update_fix_missing_schema();
    $this->assertTrue(TRUE);
  }

  /**
   * Tests update_system_schema_requirements() function.
   */
  public function testUpdateSystemSchemaRequirements() {
    $this->expectDeprecation('update_system_schema_requirements() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\DeprecatedUpdate::systemSchemaRequirements() instead. See https://www.drupal.org/node/3013060');
    include $this->root . '/core/includes/install.inc';
    $requirements = update_system_schema_requirements();
    $this->assertEquals('The installed schema version does not meet the minimum.', $requirements['minimum schema']['value']);
  }

  /**
   * Tests update_check_requirements() function.
   */
  public function testUpdateCheckRequirements() {
    $this->expectDeprecation('update_check_requirements() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::getRequirements() instead. See https://www.drupal.org/node/3013060');
    $requirements = update_check_requirements();
    $this->assertEquals('The installed schema version meets the minimum.', $requirements['minimum schema']['value']);
  }

  /**
   * Tests update_do_one() function.
   */
  public function testUpdateDoOne() {
    $this->expectDeprecation('update_do_one() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::doOne() instead. See https://www.drupal.org/node/3013060');
    $module = 'system';
    $update = self::UPDATE_N;
    $context = [
      'finished' => 0,
    ];
    update_do_one($module, $update, [], $context);

    $expected = t('Updating @module', ['@module' => $module]);
    $this->assertEquals($expected, $context['message']);
  }

  /**
   * Tests update_invoke_post_update() function.
   */
  public function testUpdateInvokePostUpdate() {
    $this->expectDeprecation('update_invoke_post_update() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::invokePostUpdate() instead. See https://www.drupal.org/node/3013060');
    $module = 'system';
    $function = $module . '_post_update_' . self::UPDATE_N;
    $context = [];
    update_invoke_post_update($function, $context);

    $expected = t('Post updating @module', ['@module' => $module]);
    $this->assertEquals($expected, $context['message']);
  }

  /**
   * Tests update_get_update_list() function.
   */
  public function testUpdateGetUpdateList() {
    $this->expectDeprecation('update_get_update_list() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::getList() instead. See https://www.drupal.org/node/3013060');
    $this->assertEquals([], update_get_update_list());
  }

  /**
   * Tests update_resolve_dependencies() function.
   */
  public function testUpdateResolveDependencies() {
    $this->expectDeprecation('update_resolve_dependencies() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::resolveDependencies() instead. See https://www.drupal.org/node/3013060');
    $starting_updates = [
      'system' => self::UPDATE_N,
    ];
    $update_graph = update_resolve_dependencies($starting_updates);
    $this->assertEquals([], $update_graph);
  }

  /**
   * Tests update_get_update_function_list() function.
   */
  public function testUpdateGetUpdateFunctionList() {
    $this->expectDeprecation('update_get_update_function_list() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::getUpdateFunctionList() instead. See https://www.drupal.org/node/3013060');
    $module = 'system';
    $starting_updates = [
      $module => self::UPDATE_N,
    ];
    $update_graph = update_get_update_function_list($starting_updates);
    $this->assertEquals(
      [$module => []],
      $update_graph
    );
  }

  /**
   * Tests update_build_dependency_graph() function.
   */
  public function testUpdateBuildDependencyGraph() {
    $this->expectDeprecation('update_build_dependency_graph() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::buildDependencyGraph() instead. See https://www.drupal.org/node/3013060');
    $module = 'system';
    $update = self::UPDATE_N;
    $update_functions = [
      $module => [
        $update => $module . '_update_' . $update,
      ],
    ];
    $update_graph = update_build_dependency_graph($update_functions);
    $this->assertEquals(
      [
        $module . '_update_' . $update => [
          'module' => $module,
          'number' => $update,
        ],
      ],
      $update_graph
    );
  }

  /**
   * Tests update_is_missing() function.
   */
  public function testUpdateIsMissing() {
    $this->expectDeprecation('update_is_missing() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::isMissing() instead. See https://www.drupal.org/node/3013060');
    $module = 'system';
    $update = self::UPDATE_N;
    $update_functions = [
      $module => [
        $update => $module . '_update_' . $update,
      ],
    ];
    $this->assertTrue(update_is_missing($module, $update, $update_functions));
  }

  /**
   * Tests update_already_performed() function.
   */
  public function testUpdateAlreadyPerformed() {
    $this->expectDeprecation('update_already_performed() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::isAlreadyPerformed() instead. See https://www.drupal.org/node/3013060');
    $this->assertFalse(update_already_performed('system', self::UPDATE_N));
  }

  /**
   * Tests update_retrieve_dependencies() function.
   */
  public function testUpdateRetrieveDependencies() {
    $this->expectDeprecation('update_retrieve_dependencies() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Update\Update::retrieveDependencies() instead. See https://www.drupal.org/node/3013060');
    $this->assertEquals([], update_retrieve_dependencies());
  }

}
