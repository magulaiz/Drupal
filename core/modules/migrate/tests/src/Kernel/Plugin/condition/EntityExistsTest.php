<?php

namespace Drupal\Tests\migrate\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Plugin\migrate\condition\EntityExists;

/**
 * Tests the entity_exists condition plugin.
 *
 * @group migrate
 */
class EntityExistsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate.condition')->getDefinition('entity_exists');
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The entity_type configuration is required when using the entity_exists condition.');
    $condition = new EntityExists($configuration, 'entity_exists', $plugin_definition, $entity_type_manager);
  }

}
