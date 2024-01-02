<?php

declare(strict_types=1);

namespace Drupal\Tests\path_alias\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Tests site information form alter functionality.
 *
 * @group path_alias
 */
class SiteInformationFormAlterTest extends BrowserTestBase {

  use PathAliasTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'path_alias'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The node that is created for testing.
   */
  protected NodeInterface $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create admin user, log in admin user, and create a node type.
    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'administer site configuration',
    ]));
    $this->drupalCreateContentType(['type' => 'page']);
  }

  /**
   * Tests form alter.
   */
  public function testFormAlter(): void {
    $node = $this->drupalCreateNode();
    $this->createPathAlias('/node/' . $node->id(), '/foo');

    // Configure site information.
    $this->drupalGet('admin/config/system/site-information');
    $this->submitForm([
      'site_frontpage' => '/foo',
      'site_403' => '/foo',
      'site_404' => '/foo',
    ], 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Check that the system paths has been saved.
    $this->assertSame('/node/1', $this->config('system.site')->get('page.front'));
    $this->assertSame('/node/1', $this->config('system.site')->get('page.403'));
    $this->assertSame('/node/1', $this->config('system.site')->get('page.404'));

    // Check that only the front page path is converted to an alias.
    $page = $this->getSession()->getPage();
    $this->assertSame('/foo', $page->findField('site_frontpage')->getValue());
    $this->assertSame('/node/1', $page->findField('site_403')->getValue());
    $this->assertSame('/node/1', $page->findField('site_404')->getValue());
  }

}
