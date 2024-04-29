<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the top bar functionality.
 *
 * @group navigation
 */
class NavigationBaseTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'navigation',
    'node',
    'layout_builder',
    'field_ui',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An admin user to configure the test environment.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Node used to check top bar options.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer navigation_block',
      'administer site configuration',
      'access administration pages',
      'access navigation',
      'bypass node access',
      'configure any layout',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create a new content type and enable Layout Builder for it.
    $node_type = $this->createContentType(['type' => 'node_type']);
    LayoutBuilderEntityViewDisplay::load('node.node_type.default')
      ->enableLayoutBuilder()
      ->setOverridable()
      ->save();

    // Place the tabs block to check its presence.
    $this->drupalPlaceBlock('local_tasks_block', ['id' => 'tabs']);

    // Enable some test blocks.
    $this->node = $this->drupalCreateNode(['type' => $node_type->id()]);
  }

  /**
   * Tests the top bar visibility.
   */
  public function testLayoutBuilderLogoVisibility() {
    $this->drupalGet('/admin/config/user-interface/navigation-block');

    // The Logo acts as a 'return to site' landmark item.  It should also be seen on
    // the Layout Builder as Layout 'preview' mode disables all links.
    $this->assertSession()->elementExists('xpath', "//div[contains(@class, 'admin-toolbar__logo')]");

    // Check for additional 'Return to site' href in form.
    $link = $this->getSession()->getPage()->findLink('Return to site');
    $this->assertNotNull($link, 'The link "Return to site" is present on the layout builder.');

  }

}
