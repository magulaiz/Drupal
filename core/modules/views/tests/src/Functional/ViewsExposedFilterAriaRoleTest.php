<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Functional;

/**
 * Tests accessibility attributes in Views exposed filter blocks.
 *
 * @group views
 */
class ViewsExposedFilterAriaRoleTest extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'views',
    'node',
    'block',
    'views_test_config',
  ];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_exposed_block'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['views_test_config']): void {
    parent::setUp(TRUE, $modules);

    $this->enableViewsTestModule();

  }

  /**
   * Tests aria-describedby attribute handling in Views exposed filter blocks.
   */
  public function testViewsExposedFilterAriaDescribedby(): void {

    // Add role attribute to the form via hook_form_alter.
    $this->container->get('state')->set('views_test_config_form_alter', [
      '#attributes' => ['role' => 'search'],
    ]);

    // Place the exposed filter block from test_exposed_block view.
    $this->drupalPlaceBlock('views_exposed_filter_block:test_exposed_block-block_1', [
      'region' => 'content',
    ]);

    $this->drupalGet('test_exposed_block');
    $this->assertSession()->statusCodeEquals(200);

    // Verify the block is present.
    $block_element = $this->assertSession()->elementExists('css', 'div.views-exposed-form');

    // Verify the block has the role attribute.
    $this->assertEquals('search', $block_element->getAttribute('role'), 'Block has role="search".');

    // Find the block title element (h2) and get its ID.
    $title_element = $this->assertSession()->elementExists('css', 'h2');
    $title_id = $title_element->getAttribute('id');

    // Verify the block has aria-describedby set to the title ID.
    $this->assertEquals($title_id, $block_element->getAttribute('aria-describedby'), 'aria-describedby is correctly set to the title ID.');
  }

}
