<?php

//declare(strict_types=1);

namespace Drupal\Tests\views\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\views\Entity\View;

/**
 * Tests the upgrade path for converting format_plural from integer to boolean.
 *
 * @group Update
 *
 * @see views_post_update_format_plural()
 */
class FormatPluralUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.filled.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/format-plural.php',
    ];
  }

  /**
   * Tests that fields with the format_plural option are updated properly.
   */
  public function testViewsFieldFormatPluralConversion(): void {
    $view = View::load('test_format_plural_update');
    $data = $view->toArray();
    $this->assertEquals('0', $data['display']['default']['display_options']['fields']['uid']['format_plural']);
    $this->assertNotEquals('false', $data['display']['default']['display_options']['fields']['uid']['format_plural']);

    $this->runUpdates();

    $view = View::load('test_format_plural_update');
    $data = $view->toArray();
    $this->assertEquals('false', $data['display']['default']['display_options']['fields']['uid']['format_plural']);
    $this->assertNotEquals('0', $data['display']['default']['display_options']['fields']['uid']['format_plural']);
  }

}
