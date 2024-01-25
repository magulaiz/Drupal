<?php

namespace Drupal\Tests\views_ui\Functional;

use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Tests the to disabled display id's screen reader text.
 *
 * @group views
 */
class DisabledActiveIdsTest extends ViewTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = [
    'test_disabled_display',
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'views_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['views_test_config']) : void {
    parent::setUp($import_test_views, $modules);
    $admin_user = $this->drupalCreateUser([
      'administer views',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests that views display id's screen reader text.
   */
  public function testDisabledActiveIds() {

    // The displays defined in this view.
    $display_ids = [
      'attachment_1',
      'block_1',
      'embed_1',
      'feed_1',
      'page_1',
    ];

    // Load the test view and initialize its displays.
    $view = $this->container->get('entity_type.manager')->getStorage('view')->load('test_disabled_display');
    $view->getExecutable()->setDisplay();

    // Disable each display id's, save and test the screen reader text
    // Is appended to the active display title in the view.
    foreach ($display_ids as $display_id) {
      $view->getExecutable()->displayHandlers->get($display_id)->setOption('enabled', FALSE);
      $view->save();
      $this->assertFalse($view->getExecutable()->displayHandlers->get($display_id)->isEnabled());
      $this->drupalGet('admin/structure/views/view/test_disabled_display/edit/' . $display_id);
      $this->assertSession()->pageTextContains('(disabled active tab)');
    }
  }

}
