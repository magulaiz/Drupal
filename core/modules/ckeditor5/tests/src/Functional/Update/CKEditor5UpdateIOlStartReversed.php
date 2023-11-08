<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Functional\Update;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\editor\Entity\Editor;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @covers ckeditor5_post_update_list_start_reversed
 * @group Update
 * @group ckeditor5
 */
class CKEditor5UpdateIOlStartReversed extends UpdatePathTestBase {

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
      __DIR__ . '/../../../fixtures/update/ckeditor5-3396628.php',
    ];
  }

  /**
   * Test that sites with <ol start> or <ol reversed> opt in to the expanded UI.
   */
  public function testUpdate(): void {
    $before = Editor::loadMultiple();
    $this->assertSame([
      'basic_html',
      'full_html',
      'test_format_list_ol_start',
      'test_text_format',
    ], array_keys($before));

    // Basic HTML before: only <ol type> editable via Source Editing.
    $settings = $before['basic_html']->getSettings();
    $this->assertArrayHasKey('ckeditor5_list', $settings['plugins']);
    $source_editable = HTMLRestrictions::fromString(implode(' ', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']));
    $this->assertSame(['type' => TRUE], $source_editable->getAllowedElements()['ol']);

    // Full HTML before: nothing listed for Source Editing.
    $settings = $before['full_html']->getSettings();
    $this->assertArrayHasKey('ckeditor5_list', $settings['plugins']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_ol_start before: nothing listed for Source Editing.
    $settings = $before['test_format_list_ol_start']->getSettings();
    $this->assertArrayHasKey('ckeditor5_list', $settings['plugins']);
    $this->assertSame(['<ol start foo>'], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_text_format before: not using the List plugin.
    $settings = $before['test_text_format']->getSettings();
    $this->assertArrayNotHasKey('ckeditor5_list', $settings['plugins']);

    $this->runUpdates();

    $after = Editor::loadMultiple();

    // Basic HTML after: reversed=FALSE, startIndex=FALSE, Source Editing
    // configuration unchanged.
    $settings = $after['basic_html']->getSettings();
    $this->assertSame(['reversed' => FALSE, 'startIndex' => FALSE], $settings['plugins']['ckeditor5_list']['properties']);
    $source_editable = HTMLRestrictions::fromString(implode(' ', $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']));
    $this->assertSame(['type' => TRUE], $source_editable->getAllowedElements()['ol']);

    // Full HTML after: reversed=TRUE, startIndex=TRUE, and Source Editing
    // configuration is unchanged.
    $settings = $after['full_html']->getSettings();
    $this->assertNotSame($before['full_html']->getSettings(), $after['full_html']->getSettings());
    $this->assertSame(['reversed' => TRUE, 'startIndex' => TRUE], $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertSame([], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_format_list_ol_type after: reversed=TRUE, startIndex=TRUE, and
    // Source Editing configuration has been updated.
    $this->assertNotSame($before['test_format_list_ol_start']->getSettings(), $after['test_format_list_ol_start']->getSettings());
    $settings = $after['test_format_list_ol_start']->getSettings();
    $this->assertSame(['reversed' => FALSE, 'startIndex' => TRUE], $settings['plugins']['ckeditor5_list']['properties']);
    $this->assertSame(['<ol foo>'], $settings['plugins']['ckeditor5_sourceEditing']['allowed_tags']);

    // test_text_format after: no changes.
    $this->assertSame($before['test_text_format']->getSettings(), $after['test_text_format']->getSettings());
  }

}
