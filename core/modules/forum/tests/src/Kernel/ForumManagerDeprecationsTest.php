<?php

namespace Drupal\Tests\forum\Kernel;

use Drupal\forum\ForumManager;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that deprecation messages are raised for deprecations.
 *
 * @coversDefaultClass \Drupal\forum\ForumManager
 * @group forum
 * @group legacy
 *
 * @todo Remove this class once deprecations are removed.
 */
class ForumManagerDeprecationsTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'options',
    'comment',
    'taxonomy',
    'forum',
  ];

  /**
   * Tests that deprecations are raised for missing constructor arguments.
   *
   * @covers \Drupal\forum\ForumManager::__construct
   * @group legacy
   */
  public function testConstructorDeprecation(): void {

    $this->expectDeprecation('Calling ' . ForumManager::class . '::__construct() without the $current_user argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/145353.');

    new ForumManager(
      $this->container->get('config.factory'),
      $this->container->get('entity_type.manager'),
      $this->container->get('database'),
      $this->container->get('string_translation'),
      $this->container->get('comment.manager'),
      $this->container->get('entity_field.manager'),
      NULL
    );

  }

}
