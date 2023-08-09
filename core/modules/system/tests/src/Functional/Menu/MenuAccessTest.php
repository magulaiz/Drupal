<?php

namespace Drupal\Tests\system\Functional\Menu;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the route access checks on menu links.
 *
 * @group Menu
 */
class MenuAccessTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'menu_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A test user with permission to access administration pages.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testUser;

  /**
   * A test role with permission to access administration pages.
   *
   * @var \Drupal\user\RoleInterface
   */
  protected $testRole;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('system_menu_block:admin', [
      'expand_all_items' => TRUE,
    ]);
    $this->testRole = Role::load($this->drupalCreateRole(['access administration pages']));
    $this->testUser = $this->drupalCreateUser([], 'editor');
    $this->testUser->addRole($this->testRole->id());
    $this->testUser->save();
  }

  /**
   * Tests menu link for route with access check.
   *
   * @see \Drupal\menu_test\Access\AccessCheck::access()
   */
  public function testMenuBlockLinksAccessCheck() {
    $this->drupalPlaceBlock('system_menu_block:account');
    // Test that there's link rendered on the route.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->linkExists('Test custom route access check');
    // Page is still accessible but there should be no menu link.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkNotExists('Test custom route access check');
    // Test that page is no more accessible.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->statusCodeEquals(403);

    // Check for access to a restricted local task from a default local task.
    $this->drupalGet('foo/asdf');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('foo/asdf');
    $this->assertSession()->linkByHrefExists('foo/asdf/b');
    $this->assertSession()->linkByHrefNotExists('foo/asdf/c');

    // Attempt to access a restricted local task.
    $this->drupalGet('foo/asdf/c');
    $this->assertSession()->statusCodeEquals(403);
    // No tab linking to foo/asdf should be found.
    $this->assertSession()->elementNotExists('xpath', $this->assertSession()->buildXPathQuery(
      '//ul[@class="tabs primary"]/li/a[@href=:href]', [
        ':href' => Url::fromRoute('menu_test.router_test1', ['bar' => 'asdf'])->toString(),
      ]
    ));
    $this->assertSession()->linkByHrefNotExists('foo/asdf/b');
    $this->assertSession()->linkByHrefNotExists('foo/asdf/c');
  }

  /**
   * Tests access to the admin menu block page when it has no child items.
   *
   * @see \Drupal\system\Controller\SystemController::systemAdminMenuBlockPage()
   */
  public function testAdminMenuBlockPage() {
    // Test as the root user with full access.
    $this->drupalLogin($this->rootUser);
    $this->assertSession()->linkExists('System');
    $this->assertSession()->linkExists('Basic site settings');
    // Test as the test user with limited access.
    $this->drupalLogin($this->testUser);
    $this->assertSession()->linkNotExists('System');
    $this->assertSession()->linkNotExists('Basic site settings');
    $this->assertSession()->linkNotExists('Structure');
    $this->assertSession()->linkNotExists('Block layout');
    // Grant the test user access to administer blocks.
    $this->testRole->grantPermission('administer blocks')->save();
    // Reload the page and check that the newly accessible links are now visible.
    $this->getSession()->reload();
    $this->assertSession()->linkExists('Structure');
    $this->assertSession()->linkExists('Block layout');
  }

}
