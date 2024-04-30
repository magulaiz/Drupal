<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Url;
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
   * Tests that Navigation Layout Builder page has Managed Tabs and returning links.
   */
  public function testLayoutBuilderManagedTabsLinking() {
    $this->drupalGet('admin/config/user-interface/navigation/layout');

    // Check for Managed Tab links. Travel between them.
    $gotoSettings = $this->clickLink('Manage Settings');
    $this->assertSession()->pageTextContains('Logo options');
    $gotoSettings = $this->clickLink('Manage Layout');
    $this->assertSession()->pageTextContains('Edit layout for Navigation');

    // Check the Logo visibility, acts as a 'return to site' landmark.
    $this->assertSession()->elementExists('xpath', "//a[contains(@class, 'admin-toolbar__logo')]");

    // Check for 'Return to site' link in the layout builder form.
    $link = $this->getSession()->getPage()->findLink('Return to site');
    $this->assertNotNull($link, "The link 'Return to site' is present on the layout builder.");
    $front_page_path = Url::fromRoute('<front>')->toString();
    $this->assertEquals($front_page_path, $link->getAttribute('href'), "The href attribute of 'Return to site' links to <front>.");
  }

}
