<?php

declare(strict_types=1);

namespace Drupal\Tests\field_ui\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update of field_ui.settings:field_prefix value.
 *
 * @group field_ui
 * @covers \field_ui_post_update_set_field_prefix_to_thirty_characters
 */
class FieldUIPrefixUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests update of field_ui.settings:field_prefix.
   */
  public function testUpdate() {
    $this->config('field_ui.settings')->set('field_prefix', 'prefix_greater_then_thirty_characters')->save();

    $this->runUpdates();

    $prefix = $this->config('field_ui.settings')->get('field_prefix');
    $this->assertEquals(30, strlen($prefix));
    $this->assertEquals('prefix_greater_then_thirty_cha', $prefix);
  }

}
