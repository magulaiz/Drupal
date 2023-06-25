<?php

namespace Drupal\Tests\views\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests that aria-sort attribute is added when sorting via AJAX exposed forms.
 *
 * @group views
 */
class ClickSortingARIASortTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'views', 'views_test_config'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public static $testViews = ['test_content_ajax'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    ViewTestData::createTestViews(self::class, ['views_test_config']);

    // Create a Content type and two test nodes.
    $this->createContentType(['type' => 'page']);
    $this->createNode([
      'title' => 'Page A',
      'changed' => \Drupal::time()->getRequestTime();,
    ]);
    $this->createNode([
      'title' => 'Page B',
      'changed' => \Drupal::time()->getRequestTime();
      1000,
    ]);

    // Create a user privileged enough to view content.
    $user = $this->drupalCreateUser(
          [
            'administer site configuration',
            'access content',
            'access content overview',
          ]
      );
    $this->drupalLogin($user);
  }

  /**
   * Tests if sorting via AJAX adds the aria-sort attribute.
   *
   * To the "Content" View and is in ascending order upon click.
   */
  public function testClickSorting() {
    // Visit the content page.
    $this->drupalGet('test-content-ajax');

    $session_assert = $this->assertSession();

    $page = $this->getSession()->getPage();

    // Now sort by title and check if the order changed.
    $page->clickLink('Title');
    $session_assert->assertWaitOnAjaxRequest();
    $session_assert->elementAttributeContains('css', 'th.views-field-title', 'aria-sort', 'ascending');
  }

}
