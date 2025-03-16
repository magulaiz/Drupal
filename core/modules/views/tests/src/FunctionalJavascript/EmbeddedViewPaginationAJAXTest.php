<?php

namespace Drupal\Tests\views\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests ajax pager requests in embedded views.
 *
 * @group views
 */
class EmbeddedViewPaginationAJAXTest extends JavascriptTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'views', 'views_test_config', 'views_test_ajax_subscriber'];

  /**
   * @var array
   * Test Views to enable.
   */
  public static $testViews = ['test_content_ajax', 'test_view_area_ajax'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    ViewTestData::createTestViews(self::class, ['views_test_config']);

    // Create a Content type and eleven test nodes.
    $this->createContentType(['type' => 'page']);
    for ($i = 1; $i <= 55; $i++) {
      $this->createNode(['title' => 'Node ' . $i . ' content', 'changed' => $i * 1000]);
    }

    // Create a user privileged enough to view content.
    $user = $this->drupalCreateUser([
      'administer site configuration',
      'access content',
      'access content overview',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Checks if embedded views send pager requests as themselves and not as
   * enclosing view.
   */
  public function testPaginationInEmbeddedAjaxView() {
    $this->drupalGet('test-view-area-ajax');

    $session_assert = $this->assertSession();

    /**
     * Tell the event subscriber to expect a pager request sent by the
     * test_content_ajax view.
     *
     * @see \Drupal\views_test_ajax_subscriber\EventSubscriberViewsTestAjaxSubscriberEvent
     */
    \Drupal::state()->set('views_test_ajax_subscriber', 'test_content_ajax');
    $session_assert->waitForLink('Next ›')->click();
  }

}
