<?php

namespace Drupal\Tests\editor\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests configuration of image lazy load filter.
 *
 * @group editor
 */
class EditorImageLazyLoadFilterConfigurationUiTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['filter', 'editor'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with the 'administer filters' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add text format.
    $filtered_html_format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
      'filters' => [],
    ]);
    $filtered_html_format->save();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser(['administer filters']);
  }

  /**
   * Tests validation logic for filter order.
   */
  public function testLazyAfterReferenceFilterValidation(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/content/formats/manage/filtered_html');

    $page = $this->getSession()->getPage();
    $page->checkField('Track images uploaded via a Text Editor');
    $page->checkField('Lazy load tracked images uploaded via a Text Editor');
    $page->selectFieldOption('edit-filters-editor-file-reference-weight', 10);
    $page->selectFieldOption('edit-filters-editor-image-lazy-load-weight', 0);
    $page->pressButton('Save configuration');
    $assert = $this->assertSession();
    $assert->pageTextContains('The Track images uploaded via a Text Editor must be enabled and placed before the Lazy load tracked images uploaded via a Text Editor filter.');
    $page->selectFieldOption('edit-filters-editor-file-reference-weight', 0);
    $page->selectFieldOption('edit-filters-editor-image-lazy-load-weight', 10);
    $page->pressButton('Save configuration');
    $assert->pageTextContains('The text format Filtered HTML has been updated.');
  }

}
