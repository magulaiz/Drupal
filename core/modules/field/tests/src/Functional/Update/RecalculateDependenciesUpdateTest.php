<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests post update functions for recalculating display dependencies.
 *
 * @group Update
 */
class RecalculateDependenciesUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/display_dependency_update_test.php',
    ];
  }

  /**
   * @covers field_post_update_recalculate_entity_form_display_dependencies
   * @covers field_post_update_recalculate_entity_view_display_dependencies
   */
  public function testPostUpdate(): void {
    $entity_type_manager = \Drupal::entityTypeManager();
    $form_display_storage = $entity_type_manager->getStorage('entity_form_display');
    $view_display_storage = $entity_type_manager->getStorage('entity_view_display');

    $form_dependencies_1 = $form_display_storage->load('node.test_content_type.default')
      ->getDependencies();
    $this->assertNotContains('action', $form_dependencies_1['module']);
    $this->assertContains('shortcut', $form_dependencies_1['module']);
    $view_dependencies_1 = $view_display_storage->load('node.test_content_type.default')
      ->getDependencies();
    $this->assertNotContains('action', $view_dependencies_1['module']);
    $this->assertContains('shortcut', $form_dependencies_1['module']);

    $this->runUpdates();

    $form_dependencies_2 = $form_display_storage->load('node.test_content_type.default')
      ->getDependencies();
    $this->assertContains('action', $form_dependencies_2['module']);
    $this->assertContains('shortcut', $form_dependencies_2['module']);
    $view_dependencies_2 = $view_display_storage->load('node.test_content_type.default')
      ->getDependencies();
    $this->assertContains('action', $view_dependencies_2['module']);
    $this->assertContains('shortcut', $view_dependencies_2['module']);
  }

}
