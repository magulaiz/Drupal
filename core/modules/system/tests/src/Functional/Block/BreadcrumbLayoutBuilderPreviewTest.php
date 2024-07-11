<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Block;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\layout_builder\Traits\EnableLayoutBuilderTrait;

/**
 * Tests breadcrumbs in layout builder.
 *
 * @group system
 * @group layout_builder
 */
class BreadcrumbLayoutBuilderPreviewTest extends BrowserTestBase {

  use EnableLayoutBuilderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'node',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // @todo The Layout Builder UI relies on local tasks; fix in
    //   https://www.drupal.org/project/drupal/issues/2917777.
    $this->drupalPlaceBlock('local_tasks_block');

    // Add a new bundle.
    $this->createContentType(['type' => 'bundle_with_section_field']);

    // Enable layout overrides.
    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'access contextual links',
    ]));
  }

  /**
   * Tests breadcrumbs preview in Layout Builder.
   */
  public function testBreadcrumbsInLayoutBuilder(): void {
    $page = $this->getSession()->getPage();
    $node = $this->createNode([
      'type' => 'bundle_with_section_field',
      'title' => 'The first node title',
    ]);
    $this->drupalGet($node->toUrl()->toString() . '/layout');
    $page->clickLink('Add block');
    $page->clickLink('Breadcrumbs');
    $page->pressButton('Add block');
    $this->assertSession()->pageTextContains('"Breadcrumbs" block');
    $page->pressButton('Save layout');
    $this->assertSession()->linkExists('Home');
    $this->assertSession()->pageTextNotContains('"Breadcrumbs" block');
  }

}
