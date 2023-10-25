<?php

namespace Drupal\Tests\ckeditor5\Functional\Update;

use Drupal\editor\Entity\Editor;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @covers ckeditor5_post_update_list_type
 * @group Update
 * @group ckeditor5
 */
class CKEditor5UpdateListTypeTest extends UpdatePathTestBase {

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
   * Test that sites with <ol type> or <ul type> opt in to the expanded UI.
   */
  public function testUpdateCodeBlockConfigurationPostUpdate(): void {
    // Basic HTML before: <ol type> and <ul type> editable via Source Editing.
    $editor_basic = Editor::load('basic_html');
    $settings = $editor_basic->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertContains('<ul type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);
    $this->assertContains('<ol type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // Full HTML before: nothing listed for Source Editing.
    $editor_full = Editor::load('full_html');
    $settings = $editor_full->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    $this->runUpdates();

    // Basic HTML after: new "styles" setting enabled, and Source Editing
    // configuration has been updated.
    $editor_basic = Editor::load('basic_html');
    $settings = $editor_basic->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertSame(TRUE, $settings['plugins']['ckeditor5_list']['styles']);
    $this->assertNotContains('<ul type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);
    $this->assertNotContains('<ol type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // Basic HTML after: new "styles" setting enabled, and Source Editing
    // configuration is unchanged.
    $editor_full = Editor::load('full_html');
    $settings = $editor_full->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertSame(TRUE, $settings['plugins']['ckeditor5_list']['styles']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);
  }

}
