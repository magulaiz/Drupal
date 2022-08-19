<?php

namespace Drupal\Tests\editor\Functional\Update;

use Drupal\editor\Entity\Editor;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for CKEditor 4 to 5.
 *
 * @group Update
 */
class CKEditor4To5Upgrade extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
    ];
  }

  /**
   * Ensures the automatic upgrade occurred.
   *
   * @see \Drupal\Tests\ckeditor5\Kernel\SmartDefaultSettingsTest
   */
  public function test() {
    $editor = Editor::load('basic_html');
    $this->assertSame('ckeditor', $editor->getEditor());

    $this->runUpdates();

    $editor = Editor::load('basic_html');
    $this->assertSame('ckeditor5', $editor->getEditor());
    // @todo Also ensure text format updates are retained!

    $this->assertSession()->pageTextContains('Updated 3 Text Editors that used CKEditor 4 to use CKEditor 5 instead (basic_html, full_html, test_text_format).');
  }

}
