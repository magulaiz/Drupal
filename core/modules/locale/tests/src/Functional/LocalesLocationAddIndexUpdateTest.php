<?php

declare(strict_types=1);

namespace Drupal\Tests\locale\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests Locale update functions.
 */
class LocalesLocationAddIndexUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles[] = $this->root . '/core/modules/system/tests/fixtures/update/drupal-10.3.0.filled.standard.php.gz';
  }

  /**
   * Tests locale_update_11000().
   */
  public function testIndex(): void {
    $this->assertFalse(\Drupal::database()
      ->schema()
      ->indexExists('locales_location', 'type_name'));

    // Run updates and test them.
    $this->runUpdates();

    $this->assertTrue(\Drupal::database()
      ->schema()
      ->indexExists('locales_location', 'type_name'));
  }

}
