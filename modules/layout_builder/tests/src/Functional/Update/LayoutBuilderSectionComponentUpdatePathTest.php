<?php

namespace Drupal\Tests\layout_builder\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for Layout Builder section components.
 *
 * @see layout_builder_post_update_section_component_third_party()
 *
 * @group layout_builder
 */
class LayoutBuilderSectionComponentUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.3.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/layout-builder.php',
      __DIR__ . '/../../../fixtures/update/layout-builder-section-components.php',
    ];
  }

  /**
   * Tests the upgrade path for Layout Builder section components.
   */
  public function testRunUpdates() {
    // Must query the DB directly as there isn't an API to retrieve a section
    // component without at least an empty third_party_settings appended.
    $query = \Drupal::database()
      ->select('config')
      ->fields('config', ['data'])
      ->condition('collection', '')
      ->condition('name', 'core.entity_view_display.node.article.teaser');
    $display = $query->execute()
      ->fetchField();
    $display = unserialize($display);
    $this->assertArrayNotHasKey('third_party_settings', $display['third_party_settings']['layout_builder']['sections'][0]['components']['f99928d0-fb60-40ed-b8df-9d11b7a2be6e']);

    $this->runUpdates();

    $display = $query->execute()
      ->fetchField();
    $display = unserialize($display);
    $this->assertArrayHasKey('third_party_settings', $display['third_party_settings']['layout_builder']['sections'][0]['components']['f99928d0-fb60-40ed-b8df-9d11b7a2be6e']);
  }

}
