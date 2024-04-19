<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for converting numeric arguments to entity_target_id.
 *
 * @group Update
 *
 * @see views_post_update_views_data_argument_plugin_id()
 */
class EntityArgumentUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
      __DIR__ . '/../../../../tests/fixtures/update/entity-id-argument.php',
    ];
  }

  /**
   * Tests that numeric argument plugins are updated properly.
   */
  public function testViewsFieldPluginConversion(): void {
    $config = \Drupal::config('views.view.test_entity_id_argument_update');
    $this->assertNotEquals('entity_target_id', $config->get('display.default.display_options.arguments.field_tags_target_id.plugin_id'));
    $this->assertNotEquals('taxonomy_term', $config->get('display.default.display_options.arguments.field_tags_target_id.target_entity_type_id'));

    $this->runUpdates();

    $config = \Drupal::config('views.view.test_entity_id_argument_update');
    $this->assertEquals('entity_target_id', $config->get('display.default.display_options.arguments.field_tags_target_id.plugin_id'));
    $this->assertEquals('taxonomy_term', $config->get('display.default.display_options.arguments.field_tags_target_id.target_entity_type_id'));
  }

}
