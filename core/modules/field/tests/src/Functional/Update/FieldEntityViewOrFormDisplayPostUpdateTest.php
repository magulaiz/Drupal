<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests recalculating dependencies of form and view displays.
 *
 * @group Update
 */
class FieldEntityViewOrFormDisplayPostUpdateTest extends UpdatePathTestBase {

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
    ];
  }

  /**
   * @covers field_post_update_resave_all_entity_view_or_form_displays
   */
  public function testEntityViewOrFormDisplaysResave(): void {
  }

}
