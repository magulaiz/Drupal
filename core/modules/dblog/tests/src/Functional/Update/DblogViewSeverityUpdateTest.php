<?php

namespace Drupal\Tests\dblog\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for modifying dblog row styles.
 *
 * @group Update
 */
class DblogViewSeverityUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests that dblog view row style is updated properly.
   *
   * @group legacy
   */
  public function testSeverityUpdate() {
    $this->runUpdates();

    $view = \Drupal::entityTypeManager()->getStorage('view')->load('watchdog');
    $display = $view->getDisplay('default');
    $style = $display['display_options']['style']['options']['row_class'];
    $this->assertStringContainsString('severity-{{ severity }}', $style);
  }

}
