<?php

namespace Drupal\Tests\views\Kernel\Plugin;

use Drupal\block\Entity\Block;
use Drupal\Core\Url;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\views\Tests\ViewTestData;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * Tests native behaviors of the block views plugin.
 *
 * @group views
 */
class ViewsBlockTest extends ViewsKernelTestBase {

  use UserCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'block_test_views', 'views_ui'];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_view_block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp();

    ViewTestData::createTestViews(static::class, ['block_test_views']);
  }

  /**
   * Tests that ViewsBlock::getMachineNameSuggestion() produces the right value.
   *
   * @see \Drupal\views\Plugin\Block::getMachineNameSuggestion()
   */
  public function testMachineNameSuggestion() {
    $plugin_definition = [
      'provider' => 'views',
    ];
    $plugin_id = 'views_block:test_view_block-block_1';
    $views_block = ViewsBlock::create($this->container, [], $plugin_id, $plugin_definition);

    $this->assertEquals('views_block__test_view_block_block_1', $views_block->getMachineNameSuggestion());
  }

  /**
   * Tests that ViewsBlock::build() produces the right output with title tokens.
   *
   * @see \Drupal\views\Plugin\Block::build()
   */
  public function testBuildWithTitleToken() {
    $view = Views::getView('test_view_block');
    $view->setDisplay();

    $sorts = [
      'name' => [
        'id' => 'name',
        'field' => 'name',
        'table' => 'views_test_data',
        'plugin_id' => 'standard',
        'order' => 'asc',
      ],
    ];
    // Set the title to the 'name' field in the first row and add a sort order
    // for consistent results on different databases.
    $view->display_handler->setOption('title', '{{ name }}');
    $view->display_handler->setOption('sorts', $sorts);
    $view->save();

    $plugin_definition = [
      'provider' => 'views',
    ];
    $plugin_id = 'views_block:test_view_block-block_1';
    $views_block = ViewsBlock::create($this->container, [], $plugin_id, $plugin_definition);

    $build = $views_block->build();
    $this->assertEquals('George', $build['#title']['#markup']);
  }

  /**
   * Tests ViewsBlock::build() with a title override.
   *
   * @see \Drupal\views\Plugin\Block::build()
   */
  public function testBuildWithTitleOverride() {
    $view = Views::getView('test_view_block');
    $view->setDisplay();

    // Add a fixed argument that sets a title and save the view.
    $view->displayHandlers->get('default')->overrideOption('arguments', [
      'name' => [
        'default_action' => 'default',
        'title_enable' => TRUE,
        'title' => 'Overridden title',
        'default_argument_type' => 'fixed',
        'default_argument_options' => [
          'argument' => 'fixed',
        ],
        'validate' => [
          'type' => 'none',
          'fail' => 'not found',
        ],
        'id' => 'name',
        'table' => 'views_test_data',
        'field' => 'name',
        'plugin_id' => 'string',
      ],
    ]);
    $view->save();

    $plugin_definition = [
      'provider' => 'views',
    ];
    $plugin_id = 'views_block:test_view_block-block_1';
    $views_block = ViewsBlock::create($this->container, [], $plugin_id, $plugin_definition);

    $build = $views_block->build();
    $this->assertEquals('Overridden title', $build['#title']['#markup']);
  }

  /**
   * Tests that ViewsBlock::getPreviewFallbackString() produces the right value.
   *
   * @see \Drupal\views\Plugin\Block\ViewsBlockBase::getPreviewFallbackString()
   */
  public function testGetPreviewFallbackString() {
    $plugin_definition = [
      'provider' => 'views',
    ];
    $plugin_id = 'views_block:test_view_block-block_1';
    $views_block = ViewsBlock::create($this->container, [], $plugin_id, $plugin_definition);

    $this->assertEquals('"test_view_block::block_1" views block', $views_block->getPreviewFallbackString());
  }

  /**
   * Tests the editing links for ViewsBlockBase.
   */
  public function testOperationLinks() {
    $this->setUpCurrentUser(['uid' => 0]);

    $block = Block::create([
      'plugin' => 'views_block:test_view_block-block_1',
      'region' => 'content',
      'id' => 'machine_name',
      'theme' => 'stark',
    ]);

    $links = $block->getOperationLinks();
    $view_link = [
      'title' => 'Edit view',
      'url' => Url::fromRoute('entity.view.edit_display_form', [
        'view' => 'test_view_block',
        'display_id' => 'block_1',
      ]),
      'weight' => 50,
    ];

    // At this point, we are logged in as anonymous, so the user doesn't
    // have the "administer views" permission.
    $this->assertEmpty($links);

    $this->setUpCurrentUser(['uid' => 1], ['administer views']);

    $links = $block->getOperationLinks();
    // At this point, we are logged in as an admin, so the user does
    // have the "administer views" permission.
    $this->assertEquals(['view-edit' => $view_link], $links);
  }

}
