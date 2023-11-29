<?php

declare(strict_types=1);

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
      __DIR__ . '/../../../fixtures/update/ckeditor5-3274635.php',
    ];
  }

  /**
   * Test that sites with <ol type> or <ul type> opt in to the expanded UI.
   */
  public function testUpdateCodeBlockConfigurationPostUpdate(): void {
    $before = Editor::loadMultiple();
    $this->assertSame([
      'basic_html',
      'full_html',
      'test_format_list_no_type',
      'test_format_list_ol_type',
      'test_text_format',
    ], array_keys($before));

    // Basic HTML before: <ol type> and <ul type> editable via Source Editing.
    $settings = $before['basic_html']->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertContains('<ul type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);
    $this->assertContains('<ol type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // Full HTML before: nothing listed for Source Editing.
    $settings = $before['full_html']->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']['properties'] ?? $settings['plugins']['ckeditor5_list']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_ol_type before: only <ol type> editable via Source
    // Editing.
    $settings = $before['test_format_list_ol_type']->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertSame(['<ol type foo>'], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_no_type before: neither <ol type> nor <ul type> editable
    // via Source Editing.
    $settings = $before['test_format_list_no_type']->getSettings();
    $this->assertArrayNotHasKey('styles', $settings['plugins']['ckeditor5_list']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_text_format before: not using the List plugin.
    $settings = $before['test_text_format']->getSettings();
    $this->assertArrayNotHasKey('ckeditor5_list', $settings['plugins']);

    $this->runUpdates();

    $after = Editor::loadMultiple();

    // Basic HTML after: new "styles" setting enabled, and Source Editing
    // configuration has been updated.
    $this->assertNotSame($before['basic_html']->getSettings(), $after['basic_html']->getSettings());
    $settings = $after['basic_html']->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertTrue($settings['plugins']['ckeditor5_list']['properties']['styles']);
    $this->assertNotContains('<ul type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);
    $this->assertNotContains('<ol type>', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // Full HTML after: new "styles" setting enabled, and Source Editing
    // configuration is unchanged.
    $this->assertNotSame($before['full_html']->getSettings(), $after['full_html']->getSettings());
    $settings = $after['full_html']->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertTrue($settings['plugins']['ckeditor5_list']['properties']['styles']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_ol_type after: new "styles" setting enabled, and Source
    // Editing configuration has been updated.
    $this->assertNotSame($before['test_format_list_ol_type']->getSettings(), $after['test_format_list_ol_type']->getSettings());
    $settings = $after['test_format_list_ol_type']->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertTrue($settings['plugins']['ckeditor5_list']['properties']['styles']);
    $this->assertSame(['<ol foo>'], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_no_type after: new "styles" setting added, but disabled.
    $settings = $after['test_format_list_no_type']->getSettings();
    $this->assertArrayHasKey('styles', $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertFalse($settings['plugins']['ckeditor5_list']['properties']['styles']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_text_format after: no changes.
    $this->assertSame($before['test_text_format']->getSettings(), $after['test_text_format']->getSettings());
  }

}
