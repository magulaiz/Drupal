<?php

namespace Drupal\Tests\editor\Functional\Update;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\editor\EditorInterface;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Tweak the basic_html text format slightly to allow testing that not only
    // the text editor but also the text format is updated by the upgrade path.
    $format = FilterFormat::load('basic_html');
    $config = $format->filters()->getConfiguration()['filter_html'];
    $config['settings']['allowed_html'] .= ' <pre>';
    $format->setFilterConfig('filter_html', $config)
      ->trustData()
      ->save();
  }


  /**
   * Ensures the automatic upgrade occurred.
   *
   * @see \Drupal\Tests\ckeditor5\Kernel\SmartDefaultSettingsTest
   */
  public function test() {
    $get_allowed_html = function (EditorInterface $editor): string {
      return $editor->getFilterFormat()->filters()->getConfiguration()['filter_html']['settings']['allowed_html'];
    };

    // Before: basic_html uses CKEditor 4.
    $editor = Editor::load('basic_html');
    $this->assertSame('ckeditor', $editor->getEditor());
    $allowed_html_before = $get_allowed_html($editor);

    $this->runUpdates();

    // After: basic_html uses CKEditor 5 and its filter_html configuration has
    // been updated to allow `<code class="language-*">`.
    $editor = Editor::load('basic_html');
    $this->assertSame('ckeditor5', $editor->getEditor());
    $allowed_html_after = $get_allowed_html($editor);
    $this->assertNotSame($allowed_html_before, $allowed_html_after);
    $before = HTMLRestrictions::fromString($allowed_html_before);
    $after = HTMLRestrictions::fromString($allowed_html_after);
    $this->assertSame([
      // Because the ckeditor5_codeBlock CKEditor 5 plugin is necessary to
      // support `<pre>`, but it also supports `<code class="language-*">`, so
      // this was added automatically to the text format's allowed HTML during
      // the automatic upgrade path.
      '<code class="language-*">',
      // Because for some reason the 9.4.0 fixture did not correctly install
      // core/profiles/standard/config/install/filter.format.basic_html.yml.
      '<img data-entity-uuid data-entity-type>',
    ], $after->diff($before)->toCKEditor5ElementsArray());

    $this->assertSession()->pageTextContains('Updated 3 Text Editors that used CKEditor 4 to use CKEditor 5 instead (basic_html, full_html, test_text_format).');
  }

}
