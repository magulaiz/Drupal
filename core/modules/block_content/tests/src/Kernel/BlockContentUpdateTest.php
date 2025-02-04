<?php

namespace Drupal\Tests\block_content\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Entity\View;

/**
 * Tests the block content update hook.
 *
 * @group block_content
 */
class BlockContentUpdateTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'block_content',
    'views',
    'user',
    'system',
    'config',
  ];

  /**
   * The test view.
   *
   * @var \Drupal\views\Entity\View
   */
  protected $view;

  /**
   * Setup test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('view');
    $this->installConfig(['block_content', 'views']);

    // Create a test view configuration with "area_text_custom".
    $this->view = View::create([
      'id' => 'block_content',
      'label' => 'Block Content Test View',
      'module' => 'views',
      'status' => TRUE,
      'display' => [
        'default' => [
          'display_options' => [
            'empty' => [
              'area_text_custom' => [
                'id' => 'text_custom',
                'content' => 'There are no content blocks available.',
              ],
            ],
          ],
        ],
      ],
    ]);
    $this->view->save();
  }

  /**
   * Tests the update function.
   */
  public function testUpdateHook(): void {
    // Ensure the plugin exists before the update.
    $this->assertTrue(isset($this->view->get('display')['default']['display_options']['empty']['area_text_custom']));

    // Run the update function.
    block_content_post_update_10301();

    $updated_view = View::load('block_content');

    // Verify the plugin is removed if it contains the default text.
    $this->assertFalse(isset($updated_view->get('display')['default']['display_options']['empty']['area_text_custom']));
  }

  /**
   * Tests that a customized text area is NOT removed.
   */
  public function testUpdateHookPreservesCustomText(): void {
    $this->view->set('display.default.display_options.empty.area_text_custom.content', 'My custom message.');
    $this->view->save();

    block_content_post_update_10301();

    $updated_view = View::load('block_content');

    $this->assertTrue(isset($updated_view->get('display')['default']['display_options']['empty']['area_text_custom']));
  }
}
